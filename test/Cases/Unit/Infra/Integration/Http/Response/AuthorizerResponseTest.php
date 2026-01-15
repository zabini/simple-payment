<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Infra\Integration\Http\Response;

use App\Core\Domain\Exceptions\InvalidOperation;
use App\Infra\Integration\Http\Response\AuthorizerResponse;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \App\Infra\Integration\Http\Response\AuthorizerResponse
 * @covers \App\Infra\Integration\Http\Concerns\ResponseHandler
 * @internal
 */
class AuthorizerResponseTest extends TestCase
{
    public function testAuthorizationDeniedFlag(): void
    {
        $exception = new RequestException(
            'denied',
            new Request('GET', '/api/v2/authorize'),
            new Response(403, [], json_encode(['data' => ['authorization' => false]]))
        );

        $this->expectException(InvalidOperation::class);
        $this->expectExceptionMessage('External Authorization Failed');

        AuthorizerResponse::failure($exception);
    }

    public function testMessageFromPayloadIsMapped(): void
    {
        $exception = new RequestException(
            'denied',
            new Request('GET', '/api/v2/authorize'),
            new Response(403, [], json_encode(['message' => 'blocked by fraud'])),
        );

        $this->expectException(InvalidOperation::class);
        $this->expectExceptionMessage('blocked by fraud');

        AuthorizerResponse::failure($exception);
    }

    public function testUnmappedExceptionIsWrapped(): void
    {
        $exception = new RuntimeException('boom');

        $this->expectException(InvalidOperation::class);
        $this->expectExceptionMessage('Failed to authorize');

        AuthorizerResponse::failure($exception);
    }

    public function testInvalidJsonResponseFallsBackToUnmapped(): void
    {
        $exception = new RequestException(
            'invalid json',
            new Request('GET', '/api/v2/authorize'),
            new Response(500, [], 'not-json')
        );

        $this->expectException(InvalidOperation::class);
        $this->expectExceptionMessage('Failed to authorize');

        AuthorizerResponse::failure($exception);
    }
}
