<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Infra\Integration\Http;

use App\Core\Domain\Exceptions\NotificationException;
use App\Infra\Exception\GatewayTimeoutException;
use App\Infra\Integration\Http\Client\Notifier;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RuntimeException;

/**
 * @covers \App\Infra\Integration\Http\Client\Notifier
 * @covers \App\Infra\Integration\Http\Concerns\Client
 * @covers \App\Infra\Integration\Http\Concerns\ResponseHandler
 * @internal
 */
class NotifierClientTest extends TestCase
{
    public function testRequestNotificationSucceeds(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['ok' => true])),
        ]);

        $client = $this->makeClient($mock);

        $this->assertNull($client->requestNotication('user-1'));
    }

    public function testRequestExceptionIsConvertedToGatewayTimeout(): void
    {
        $mock = new MockHandler([
            new RequestException('timeout', new Request('POST', '/api/v1/notify')),
        ]);

        $client = $this->makeClient($mock);

        $this->expectException(GatewayTimeoutException::class);

        $client->requestNotication('user-1');
    }

    public function testGenericExceptionIsWrappedAsNotificationException(): void
    {
        $mock = new MockHandler([
            new RuntimeException('boom'),
        ]);

        $client = $this->makeClient($mock);

        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Failed to notify payee');

        $client->requestNotication('user-1');
    }

    private function makeClient(MockHandler $mock): Notifier
    {
        $stack = HandlerStack::create($mock);

        return new Notifier(
            logger: new NullLogger(),
            baseUri: 'http://example.test',
            extras: ['handler' => $stack]
        );
    }
}
