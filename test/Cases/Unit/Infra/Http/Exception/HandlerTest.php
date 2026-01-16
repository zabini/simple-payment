<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Infra\Http\Exception;

use App\Core\Domain\Exceptions\DomainException;
use App\Infra\Http\Exception\Handler;
use Hyperf\Contract\MessageBag;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\Validation\ValidationException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use ReflectionProperty;
use RuntimeException;

/**
 * @covers \App\Infra\Http\Exception\Handler
 * @internal
 */
class HandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testReturnsValidationErrorResponse(): void
    {
        $handler = new Handler();
        $capturedPayload = null;

        $response = Mockery::mock(HttpResponse::class);
        $psrResponse = Mockery::mock(ResponseInterface::class);

        $response->shouldReceive('json')
            ->once()
            ->with(Mockery::on(function ($payload) use (&$capturedPayload) {
                $capturedPayload = $payload;

                return true;
            }))
            ->andReturn($psrResponse);

        $psrResponse->shouldReceive('withStatus')
            ->once()
            ->with(422)
            ->andReturn($psrResponse);

        $this->setHandlerDependencies($handler, $response, Mockery::mock(StdoutLoggerInterface::class));

        $errors = Mockery::mock(MessageBag::class);
        $errors->shouldReceive('toArray')->andReturn(['email' => ['The email field is required.']]);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->shouldReceive('errors')->andReturn($errors);

        $exception = new class($validator) extends ValidationException {
            public function __construct($validator)
            {
                $this->validator = $validator;
            }
        };

        $result = $handler->handle($exception, Mockery::mock(ResponseInterface::class));

        $this->assertSame($psrResponse, $result);
        $this->assertSame([
            'success' => false,
            'code' => 'VALIDATION_ERROR',
            'message' => 'Validation failed.',
            'errors' => ['email' => ['The email field is required.']],
        ], $capturedPayload);
    }

    public function testReturnsDomainErrorResponse(): void
    {
        $handler = new Handler();
        $capturedPayload = null;

        $response = Mockery::mock(HttpResponse::class);
        $psrResponse = Mockery::mock(ResponseInterface::class);

        $response->shouldReceive('json')
            ->once()
            ->with(Mockery::on(function ($payload) use (&$capturedPayload) {
                $capturedPayload = $payload;

                return true;
            }))
            ->andReturn($psrResponse);

        $psrResponse->shouldReceive('withStatus')
            ->once()
            ->with(409)
            ->andReturn($psrResponse);

        $this->setHandlerDependencies($handler, $response, Mockery::mock(StdoutLoggerInterface::class));

        $exception = new DomainException(
            message: 'Business rule violated',
            errorCode: 'BUSINESS_FAIL',
            statusCode: 409,
            errors: ['field' => ['invalid']]
        );

        $result = $handler->handle($exception, Mockery::mock(ResponseInterface::class));

        $this->assertSame($psrResponse, $result);
        $this->assertSame([
            'success' => false,
            'code' => 'BUSINESS_FAIL',
            'message' => 'Business rule violated',
            'errors' => ['field' => ['invalid']],
        ], $capturedPayload);
    }

    public function testLogsAndReturnsInternalErrorForUnexpectedException(): void
    {
        $handler = new Handler();
        $capturedPayload = null;

        $response = Mockery::mock(HttpResponse::class);
        $psrResponse = Mockery::mock(ResponseInterface::class);

        $response->shouldReceive('json')
            ->once()
            ->with(Mockery::on(function ($payload) use (&$capturedPayload) {
                $capturedPayload = $payload;

                return true;
            }))
            ->andReturn($psrResponse);

        $psrResponse->shouldReceive('withStatus')
            ->once()
            ->with(500)
            ->andReturn($psrResponse);

        $logger = Mockery::mock(StdoutLoggerInterface::class);
        $logger->shouldReceive('error')
            ->once()
            ->with(Mockery::on(fn ($message) => str_contains((string) $message, 'boom')));

        $this->setHandlerDependencies($handler, $response, $logger);

        $result = $handler->handle(new RuntimeException('boom'), Mockery::mock(ResponseInterface::class));

        $this->assertSame($psrResponse, $result);
        $this->assertSame([
            'success' => false,
            'code' => 'INTERNAL_ERROR',
            'message' => 'Internal server error.',
        ], $capturedPayload);
    }

    private function setHandlerDependencies(Handler $handler, HttpResponse $response, StdoutLoggerInterface $logger): void
    {
        $responseProperty = new ReflectionProperty($handler, 'response');
        $responseProperty->setAccessible(true);
        $responseProperty->setValue($handler, $response);

        $loggerProperty = new ReflectionProperty($handler, 'logger');
        $loggerProperty->setAccessible(true);
        $loggerProperty->setValue($handler, $logger);
    }
}
