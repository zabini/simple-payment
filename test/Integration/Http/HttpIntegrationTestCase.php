<?php

declare(strict_types=1);

namespace HyperfTest\Integration\Http;

use App\Core\Domain\Contracts\Event\Publisher as PublisherInterface;
use App\Core\Domain\Contracts\TransferRepository as TransferRepositoryInterface;
use App\Core\Domain\Contracts\UserRepository as UserRepositoryInterface;
use App\Core\Domain\Contracts\WalletRepository as WalletRepositoryInterface;
use App\Core\Domain\User\UserFactory;
use Hyperf\Context\ApplicationContext;
use Hyperf\Testing\TestCase as HyperfTestCase;
use HyperfTest\Doubles\InMemoryTransferRepository;
use HyperfTest\Doubles\InMemoryUserRepository;
use HyperfTest\Doubles\InMemoryWalletRepository;
use HyperfTest\Doubles\NullPublisher;

abstract class HttpIntegrationTestCase extends HyperfTestCase
{
    protected InMemoryUserRepository $userRepository;

    protected InMemoryWalletRepository $walletRepository;

    protected InMemoryTransferRepository $transferRepository;

    protected NullPublisher $publisher;

    protected function setUp(): void
    {
        parent::setUp();

        $container = ApplicationContext::getContainer();
        $userFactory = $container->get(UserFactory::class);

        $this->userRepository = new InMemoryUserRepository($userFactory);
        $this->walletRepository = new InMemoryWalletRepository();
        $this->transferRepository = new InMemoryTransferRepository();
        $this->publisher = new NullPublisher();

        $container->set(UserRepositoryInterface::class, $this->userRepository);
        $container->set(WalletRepositoryInterface::class, $this->walletRepository);
        $container->set(TransferRepositoryInterface::class, $this->transferRepository);
        $container->set(PublisherInterface::class, $this->publisher);
    }
}
