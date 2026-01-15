<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Infra\Integration\Http;

use App\Core\Domain\Exceptions\InvalidOperation;
use App\Infra\Integration\Http\Client\Authorizer;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @covers \App\Infra\Integration\Http\Client\Authorizer
 * @covers \App\Infra\Integration\Http\Concerns\Client
 * @covers \App\Infra\Integration\Http\Concerns\ResponseHandler
 * @internal
 */
class AuthorizerClientTest extends TestCase
{
    public function testRequestAuthorizationReturnsTrue(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => ['authorization' => true]])),
        ]);

        $client = $this->makeClient($mock);

        $this->assertTrue($client->requestAuthorization('payer-1'));
    }

    public function testRequestAuthorizationReturnsFalseEvenWhenExternalApiBehavierWeird(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['data' => ['authorization' => false]])),
        ]);

        $client = $this->makeClient($mock);

        $this->assertFalse($client->requestAuthorization('payer-1'));
    }

    public function testDeniedAuthorizationThrowsMappedInvalidOperation(): void
    {
        $response = new Response(403, [], json_encode([
            'data' => ['authorization' => false],
        ]));
        $mock = new MockHandler([
            new RequestException('denied', new Request('GET', '/api/v2/authorize'), $response),
        ]);

        $client = $this->makeClient($mock);

        $this->expectException(InvalidOperation::class);
        $this->expectExceptionMessage('External Authorization Failed');

        $client->requestAuthorization('payer-1');
    }

    public function testUnmappedRequestExceptionIsWrapped(): void
    {
        $mock = new MockHandler([
            new RequestException('network error', new Request('GET', '/api/v2/authorize')),
        ]);

        $client = $this->makeClient($mock);

        $this->expectException(InvalidOperation::class);
        $this->expectExceptionMessage('Failed to authorize');

        $client->requestAuthorization('payer-1');
    }

    public function testInvalidJsonPayloadIsWrappedAsInvalidOperation(): void
    {
        $mock = new MockHandler([
            new Response(200, [], 'this-is-not-json'),
        ]);

        $client = $this->makeClient($mock);

        $this->expectException(InvalidOperation::class);
        $this->expectExceptionMessage('Transfer denied for reason: Failed to authorize: Message: Invalid JSON format | Class: App\Infra\Exception\JsonException');

        $client->requestAuthorization('payer-1');
    }

    private function makeClient(MockHandler $mock): Authorizer
    {
        $stack = HandlerStack::create($mock);
        return new Authorizer(
            logger: new NullLogger(),
            baseUri: 'http://example.test',
            extras: ['handler' => $stack]
        );
    }
}
