# Simple Payment API

API RESTful que permite transferências de dinheiro entre usuários comuns e lojistas.

## Visão geral
- Usuários podem ser criados como `common` (podem enviar e receber) ou `seller` (apenas recebem).
- Cada usuário possui uma carteira (`wallet`) criada automaticamente.
- É possível depositar valores manualmente na carteira e registrar transferências entre usuários, sempre preservando o histórico de lançamentos.
- Transferências passam por autorização externa e só são concluídas após confirmação; lojistas não podem ser pagadores, mas podem receber de qualquer usuário.
- Notificações ao recebedor são enfileiradas para processamento assíncrono.

### Fluxo resumido
1) Criar usuários (um pagador `common` e um recebedor).  
2) Depositar fundos no pagador para liberar saldo.  
3) Criar a transferência: o sistema valida regras de negócio, consulta o autorizador externo e registra lançamentos de débito/crédito nas carteiras.  
4) Ao concluir, publica um evento que agenda a notificação do recebedor em background.

## Escopo e premissas
- Escopo coberto: criação de usuário, consulta por id, depósito, criação de transferência e notificação do recebedor.
- Não há autenticação ou autorização de chamadas HTTP; foco está no domínio de pagamentos.
- As operações são síncronas até a conclusão da transferência; a notificação do recebedor é disparada de forma assíncrona.
- Dados de senha são persistidos como texto simples (mantido assim para o exercício); em produção seria obrigatório aplicar hashing, ou delegar a autenticação para um microserviço próprio para esse fim.

## Decisões técnicas
- **Framework Hyperf + Swoole**: escolhido por alinhar com a stack usada pela empresa e entregar alta performance em I/O.
- **DDD em camadas**: domínio isolado em `app/Core/Domain`, aplicação orquestrando casos de uso e infraestrutura cuidando de HTTP, persistência e integrações.
- **Saldo derivado de ledger**: o saldo da carteira é calculado a partir de lançamentos (`ledgerEntries`) para preservar histórico e rastreabilidade.
- **Eventos de domínio**: transferência criada como `pending` publica evento que aciona o processamento e, ao concluir, outro evento agenda a notificação.
- **UUIDs** para entidades, evitando dependência do banco para identidade.
- **Integrações HTTP** com serviços de autorização e notificação usando Guzzle, com tratamento de erros específico para cada parceiro.
- **Fila assíncrona (Redis)**: Hyperf Async Queue entrega o job de notificação com retentativas configuráveis.

## Tecnologias e ferramentas
- PHP 8.1+, Hyperf 3.1, Swoole, Guzzle.
- Banco relacional (PostgreSQL via `.env.example`) e Redis para filas (`async-queue`).
- Testes com PHPUnit (`composer test`), análise estática com PHPStan (`composer analyse`) e formatação com PHP-CS-Fixer (`composer cs-fix`).

### Como executar
1. `composer install` (gera `.env` a partir do `.env.example` se faltar).
2. `make dev` (builda imagem, sobe os containers e executa as migrations). Alternativa manual: `docker-compose up -d` seguido de `docker-compose exec simple-payment-api php bin/hyperf.php migrate`.
3. A API escuta em `:9501`. Em dev local, use `composer start` ou `php bin/hyperf.php server:watch` dentro do container.
4. Testes: `make tests` (ou `SKIP_TESTS=1 make tests` para pular temporariamente).

## Arquitetura e organização
- **Domínio (`app/Core/Domain`)**: entidades (`User`, `Wallet`, `Transfer`, `LedgerEntry`), enums e regras de negócio. Ex.: `Wallet::transferTo` valida saldo, reserva valores (`committedBalance`) e gera lançamentos de débito/crédito vinculados à transferência.
- **Aplicação (`app/Core/Application`)**: command e handlers que orquestram casos de uso (`User/CreateHandler`, `User/TransferHandler`, `Wallet/ProcessTransferHandler`, `Transfer/NotifyPayeeHandler`).
- **Infraestrutura (`app/Infra`)**: adaptadores HTTP (controllers + validação), repositórios (ORM Hyperf), integrações externas (autorizador e notificador), fila assíncrona e publicação de eventos.
- **Fluxo de transferência detalhado**:
  - `User/TransferHandler` carrega carteiras, valida saldo e o tipo do pagador cria uma transferência `pending` e publica o evento de domínio `Transfer/PendingCreated`.
  - `ProcessTransferHandler` , consulta o autorizador externo e grava lançamentos de débito/crédito.
  - Ao concluir, a transferência é marcada como `completed` e o evento `Completed` dispara um job que notifica o recebedor em background.
- **Rotas principais**: `POST /user`, `GET /user/{id}`, `POST /user/{id}/deposit`, `POST /transfer`.

### Diagrama de classes
![](docs/class_diagram.png)

## Exemplos de requisição (cURL)
- Criar usuário comum:
```bash
curl --location 'http://localhost:9501/user' \
  --header 'Content-Type: application/json' \
  --data-raw '{
    "full_name": "Cecelia Will V",
    "kind": "common",
    "document_type": "cpf",
    "document": "11122233344",
    "email": "Alexane37@gmail.com",
    "password": "cOwOYX2rF6zKnov"
  }'
```

- Depositar na carteira do usuário:
```bash
curl --location 'http://localhost:9501/user/b64b6362-98ea-44e1-8b9d-aaab39c2f691/deposit' \
  --header 'Content-Type: application/json' \
  --data '{
    "amount": 1000
  }'
```

- Consultar usuário por id:
```bash
curl --location 'http://localhost:9501/user/b64b6362-98ea-44e1-8b9d-aaab39c2f691'
```

- Criar transferência entre usuários:
```bash
curl --location 'http://localhost:9501/transfer' \
  --header 'Content-Type: application/json' \
  --data '{
    "amount": 10,
    "payer": "b64b6362-98ea-44e1-8b9d-aaab39c2f691",
    "payee": "a9155293-954b-46d8-a890-acd1ed8d5857"
  }'
```

## Modelagem de domínio
- **User**: especializações `Common` e `Seller`; armazena documento (CPF/CNPJ), e-mail e referencia a `Wallet`. Apenas `Common` pode transferir.
- **Wallet**: mantém `ledgerEntries`, calcula saldo atual e considera `committedBalance` para reservar valores de transferências pendentes (soma de transfers `pending` para evitar double spend).
- **LedgerEntry**: lançamentos do tipo `credit` ou `debit`, com operação `manual` ou `transfer`, opcionalmente vinculados a uma transferência.
- **Transfer**: estado (`pending`, `completed`, `failed`, `reverted`), carteiras de pagador/recebedor e motivo de falha quando existir.
- **Eventos de domínio**: `Transfer\PendingCreated` e `Transfer\Completed` orquestram processamento e notificação.

## Design patterns utilizados
- **Factory**: `UserFactory` cria instâncias coerentes de usuários e carteiras.
- **Command/Handler**: classes de comando representam ações e handlers encapsulam os casos de uso.
- **Repository Pattern**: interfaces no domínio com implementações ORM na infraestrutura.
- **Mediator / Publisher-Subscriber**: eventos de domínio desacoplam casos de uso (processamento de transferência e notificação).

## Testes e qualidade
- Testes unitários e de integração usando repositórios em memória para isolamento. Cobertura aproximada: ~35% (ponto conhecido a evoluir).
- TDD não foi seguido; os testes vieram após as primeiras iterações de código.
- Comandos: `make tests` para suíte completa, `make test-filter filter=Pattern` para filtros, `make analyse` para análise estática e `make fix` para formatação.
- Cobertura HTML opcional com `make coverage` (gera em `runtime/coverage/index.html`).

## Pontos de atenção e limitações atuais
- Crescimento do `ledgerEntries` dentro de `Wallet` pode degradar cálculo de saldo; precisa de estratégia de agregação/lazy loading para escala maior.
- Ausência de DTOs torna contratos de entrada/saída menos explícitos e acopla controllers às entidades.
- Cobertura de testes ainda baixa (~35%) e sem ciclo TDD.
- Senhas persistidas sem hashing e ausência de autenticação/autorização HTTP.
- Dependência de serviços externos (autorizador e notificador) sem circuit breaker; falhas podem bloquear processamento.

## Melhorias futuras
- Introduzir DTOs e mapeamento dedicado nas bordas (HTTP/infra) para reduzir acoplamento e validar contratos.
- Revisar estratégia de saldo (ex.: agregações periódicas, lazyload de lançamentos, cache).
- Aumentar cobertura de testes, cobrindo fluxos de erro das integrações externas e casos de concorrência.
- Implementar hashing de senha, autenticação e políticas de autorização para endpoints sensíveis.
- Adicionar resiliência às integrações (timeouts configuráveis, retries com backoff e circuit breaker) e observabilidade de eventos/fila.
