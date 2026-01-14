# Introdução

# Diagram de Classes

![Diagram](docs/class_diagram.png)


# Problemas e Melhorias

- Criar DTOs pra retornar os dados aos usuários
- link transfer o Ledger Entries
- Notificar recebedor
    - Implementar retentativas de notificação

## Problema
- considerar as transferencias pendentes de confirmação na hora de criar a transação.
- Se o usuário spammar um monte de transferencias ele pode acabar provisionando ínumeras transações, que pode extrapolar o saldo
