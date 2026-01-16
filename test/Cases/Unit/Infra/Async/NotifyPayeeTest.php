<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Infra\Async;

use App\Core\Application\Transfer\NotifyPayee as TransferNotifyPayee;
use App\Core\Application\Transfer\NotifyPayeeHandler;
use App\Infra\Async\NotifyPayee;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * @covers \App\Infra\Async\NotifyPayee
 * @internal
 */
class NotifyPayeeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function tearDown(): void
    {
        ApplicationContext::setContainer(require BASE_PATH . '/config/container.php');
        parent::tearDown();
    }

    public function testConstructorSetsMaxAttemptsFromConfig(): void
    {
        $config = Mockery::mock(ConfigInterface::class);
        $config->shouldReceive('get')
            ->once()
            ->with('integration.notifier.max_attempts', 10)
            ->andReturn(7);

        ApplicationContext::getContainer()->set(ConfigInterface::class, $config);

        $job = new NotifyPayee('transfer-abc');

        $maxAttempts = new ReflectionProperty($job, 'maxAttempts');
        $maxAttempts->setAccessible(true);

        $this->assertSame(7, $maxAttempts->getValue($job));
    }

    public function testHandleDelegatesToNotifyPayeeHandler(): void
    {
        $config = Mockery::mock(ConfigInterface::class);
        $config->shouldReceive('get')
            ->with('integration.notifier.max_attempts', 10)
            ->andReturn(10);

        ApplicationContext::getContainer()->set(ConfigInterface::class, $config);

        $handler = Mockery::mock(NotifyPayeeHandler::class);
        $handler->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(function (TransferNotifyPayee $command) {
                return $command->getTransferId() === 'transfer-999';
            }))
            ->andReturnNull();

        ApplicationContext::getContainer()->set(NotifyPayeeHandler::class, $handler);

        $job = new NotifyPayee('transfer-999');
        $job->handle();
    }
}
