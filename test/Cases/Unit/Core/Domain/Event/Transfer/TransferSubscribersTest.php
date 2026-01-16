<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Core\Domain\Event\Transfer;

use App\Core\Application\Wallet\ProcessTransfer;
use App\Core\Application\Wallet\ProcessTransferHandler;
use App\Core\Domain\Contracts\AsyncJobDispatcher;
use App\Core\Domain\Event\Transfer\Completed;
use App\Core\Domain\Event\Transfer\CompletedSubscriber;
use App\Core\Domain\Event\Transfer\PendingCreated;
use App\Core\Domain\Event\Transfer\PendingCreatedSubscriber;
use App\Infra\Async\NotifyPayee as AsyncNotifyPayee;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * @covers \App\Core\Domain\Event\Transfer\CompletedSubscriber
 * @covers \App\Core\Domain\Event\Transfer\PendingCreatedSubscriber
 * @internal
 */
class TransferSubscribersTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testCompletedSubscriberListensToCompletedEvent(): void
    {
        $subscriber = new CompletedSubscriber(Mockery::mock(AsyncJobDispatcher::class));

        $this->assertSame([Completed::class], $subscriber->listen());
    }

    public function testCompletedSubscriberDispatchesNotifyPayeeJob(): void
    {
        $jobDispatcher = Mockery::mock(AsyncJobDispatcher::class);
        $jobDispatcher->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::on(function (object $job) {
                if (! $job instanceof AsyncNotifyPayee) {
                    return false;
                }

                $property = new ReflectionProperty(AsyncNotifyPayee::class, 'transferId');
                $property->setAccessible(true);

                return $property->getValue($job) === 'transfer-123';
            }))
            ->andReturnTrue();

        $subscriber = new CompletedSubscriber($jobDispatcher);

        $subscriber->process(new Completed('transfer-123'));
    }

    public function testPendingCreatedSubscriberListensToPendingCreatedEvent(): void
    {
        $subscriber = new PendingCreatedSubscriber(Mockery::mock(ProcessTransferHandler::class));

        $this->assertSame([PendingCreated::class], $subscriber->listen());
    }

    public function testPendingCreatedSubscriberTriggersProcessTransfer(): void
    {
        $processTransferHandler = Mockery::mock(ProcessTransferHandler::class);
        $processTransferHandler->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(function (ProcessTransfer $command) {
                return $command->getTransferId() === 'transfer-456';
            }))
            ->andReturnNull();

        $subscriber = new PendingCreatedSubscriber($processTransferHandler);

        $subscriber->process(new PendingCreated('transfer-456'));
    }
}
