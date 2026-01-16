<?php

declare(strict_types=1);

namespace HyperfTest\Integration\Application;

use App\Core\Application\User\Create;
use App\Core\Application\User\CreateHandler;
use App\Core\Application\User\Deposit;
use App\Core\Application\User\DepositHandler;
use App\Core\Application\User\FetchById;
use App\Core\Application\User\FetchByIdHandler;
use App\Core\Application\User\Transfer as CreateTransfer;
use App\Core\Application\User\TransferHandler;
use App\Core\Application\Wallet\ProcessTransferHandler;
use App\Core\Domain\Contracts\Enum\DocumentType;
use App\Core\Domain\Contracts\Enum\UserKind;
use App\Core\Domain\Contracts\Event\Publisher as PublisherInterface;
use App\Core\Domain\Contracts\Notifier as NotifierInterface;
use App\Core\Domain\Contracts\TransferAuthorizer as TransferAuthorizerInterface;
use App\Core\Domain\Contracts\TransferRepository as TransferRepositoryInterface;
use App\Core\Domain\Contracts\UserRepository as UserRepositoryInterface;
use App\Core\Domain\Contracts\WalletRepository as WalletRepositoryInterface;
use App\Core\Domain\User\UserFactory;
use App\Core\Domain\Wallet;
use Hyperf\Context\ApplicationContext;
use HyperfTest\Doubles\FakeTransferAuthorizer;
use HyperfTest\Doubles\InMemoryTransferRepository;
use HyperfTest\Doubles\InMemoryUserRepository;
use HyperfTest\Doubles\InMemoryWalletRepository;
use HyperfTest\Doubles\RecordingPublisher;
use HyperfTest\Doubles\SpyNotifier;
use PHPUnit\Framework\TestCase;

abstract class ApplicationIntegrationTestCase extends TestCase
{
    protected InMemoryUserRepository $userRepository;

    protected InMemoryWalletRepository $walletRepository;

    protected InMemoryTransferRepository $transferRepository;

    protected RecordingPublisher $publisher;

    protected FakeTransferAuthorizer $transferAuthorizer;

    protected SpyNotifier $notifier;

    protected UserFactory $userFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $container = ApplicationContext::getContainer();
        $this->userFactory = $container->get(UserFactory::class);

        $this->userRepository = new InMemoryUserRepository($this->userFactory);
        $this->walletRepository = new InMemoryWalletRepository();
        $this->transferRepository = new InMemoryTransferRepository();
        $this->publisher = new RecordingPublisher();
        $this->transferAuthorizer = new FakeTransferAuthorizer();
        $this->notifier = new SpyNotifier();

        $container->set(UserRepositoryInterface::class, $this->userRepository);
        $container->set(WalletRepositoryInterface::class, $this->walletRepository);
        $container->set(TransferRepositoryInterface::class, $this->transferRepository);
        $container->set(PublisherInterface::class, $this->publisher);
        $container->set(TransferAuthorizerInterface::class, $this->transferAuthorizer);
        $container->set(NotifierInterface::class, $this->notifier);
    }

    protected function get(string $id): mixed
    {
        return ApplicationContext::getContainer()->get($id);
    }

    protected function createUser(array $override = []): string
    {
        $data = array_merge([
            'full_name' => 'User ' . uniqid(),
            'kind' => UserKind::common->value,
            'document_type' => DocumentType::cpf->value,
            'document' => (string) random_int(10000000000, 99999999999),
            'email' => sprintf('user-%s@example.com', uniqid()),
            'password' => 'password',
        ], $override);

        /** @var CreateHandler $handler */
        $handler = new CreateHandler($this->userRepository, $this->userFactory);

        return $handler->handle(
            new Create(
                $data['full_name'],
                $data['kind'],
                $data['document_type'],
                $data['document'],
                $data['email'],
                $data['password'],
            )
        );
    }

    protected function deposit(string $userId, float $amount): void
    {
        $handler = new DepositHandler($this->userRepository, $this->walletRepository);
        $handler->handle(new Deposit($userId, $amount));
    }

    protected function fetchUser(string $userId)
    {
        $handler = new FetchByIdHandler($this->userRepository);
        return $handler->handle(new FetchById($userId));
    }

    protected function createTransfer(string $payerId, string $payeeId, float $amount): string
    {
        $handler = $this->makeTransferHandler();
        return $handler->handle(new CreateTransfer($payerId, $payeeId, $amount));
    }

    protected function makeTransferHandler(): TransferHandler
    {
        $handler = new TransferHandler($this->userRepository, $this->transferRepository);
        $this->injectPublisher($handler);

        return $handler;
    }

    protected function makeProcessTransferHandler(): ProcessTransferHandler
    {
        $handler = new ProcessTransferHandler(
            $this->transferRepository,
            $this->walletRepository,
            $this->transferAuthorizer
        );
        $this->injectPublisher($handler);

        return $handler;
    }

    /**
     * @param TransferHandler|ProcessTransferHandler $handler
     */
    private function injectPublisher(object $handler): void
    {
        $reflection = new \ReflectionClass($handler);
        if ($reflection->hasProperty('publisher')) {
            $property = $reflection->getProperty('publisher');
            $property->setAccessible(true);
            $property->setValue($handler, $this->publisher);
        }
    }
}
