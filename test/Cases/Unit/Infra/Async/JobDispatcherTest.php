<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Infra\Async;

use App\Infra\Async\JobDispatcher;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \App\Infra\Async\JobDispatcher
 * @internal
 */
class JobDispatcherTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function tearDown(): void
    {
        ApplicationContext::setContainer(require BASE_PATH . '/config/container.php');
        parent::tearDown();
    }

    public function testDispatchThrowsWhenJobDoesNotImplementJobInterface(): void
    {
        $dispatcher = new JobDispatcher();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Job must implement Hyperf\AsyncQueue\JobInterface');

        $dispatcher->dispatch(new stdClass());
    }

    public function testDispatchPushesJobThroughDriverFactory(): void
    {
        $container = ApplicationContext::getContainer();

        $driver = Mockery::mock(DriverInterface::class);
        $driver->shouldReceive('push')
            ->once()
            ->withAnyArgs()
            ->andReturnTrue();

        $driverFactory = Mockery::mock(DriverFactory::class);
        $driverFactory->shouldReceive('get')
            ->once()
            ->with('custom-pool')
            ->andReturn($driver);

        $config = Mockery::mock(ConfigInterface::class);
        $config->shouldReceive('get')
            ->withAnyArgs()
            ->andReturn([]);

        $this->swapContainerEntry(ConfigInterface::class, $config);
        $this->swapContainerEntry(DriverFactory::class, $driverFactory);

        $job = Mockery::mock(JobInterface::class)->shouldIgnoreMissing();
        $dispatcher = new JobDispatcher();

        $this->assertTrue($dispatcher->dispatch($job, delay: 5, maxAttempts: 2, pool: 'custom-pool'));
    }

    private function swapContainerEntry(string $id, object $service): void
    {
        $container = ApplicationContext::getContainer();
        $container->set($id, $service);
    }
}
