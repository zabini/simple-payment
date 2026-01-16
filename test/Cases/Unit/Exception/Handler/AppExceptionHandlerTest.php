<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Exception\Handler;

use App\Exception\Handler\AppExceptionHandler;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpMessage\Server\Response;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \App\Exception\Handler\AppExceptionHandler
 * @internal
 */
class AppExceptionHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testHandleLogsExceptionAndReturnsGeneric500Response(): void
    {
        $logger = Mockery::mock(StdoutLoggerInterface::class);
        $handler = new AppExceptionHandler($logger);
        $exception = new RuntimeException('Something broke');
        $response = new Response();

        $logger->shouldReceive('error')
            ->once()
            ->with(Mockery::on(fn (string $message) => str_contains($message, 'Something broke[')));

        $logger->shouldReceive('error')
            ->once()
            ->with($exception->getTraceAsString());

        $handledResponse = $handler->handle($exception, $response);

        $this->assertSame('Hyperf', $handledResponse->getHeaderLine('Server'));
        $this->assertSame(500, $handledResponse->getStatusCode());
        $this->assertSame('Internal Server Error.', $handledResponse->getBody()->getContents());
    }

    public function testIsValidAlwaysReturnsTrue(): void
    {
        $handler = new AppExceptionHandler(Mockery::mock(StdoutLoggerInterface::class));

        $this->assertTrue($handler->isValid(new RuntimeException('Anything')));
    }
}
