<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Infra\Integration\Http;

use App\Infra\Exception\GatewayTimeoutException;
use App\Infra\Integration\Http\Client\Notifier as NotifierClient;
use App\Infra\Integration\Http\Notifier;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Infra\Integration\Http\Notifier
 * @internal
 */
class NotifierTest extends TestCase
{
    public function testNotifyDelegatesToClient(): void
    {
        $client = $this->createMock(NotifierClient::class);
        $client->expects($this->once())
            ->method('requestNotication')
            ->with('user-1');

        $notifier = new Notifier($client);
        $notifier->notify('user-1');

        $this->assertTrue(true);
    }

    public function testNotifyPropagatesExceptions(): void
    {
        $client = $this->createMock(NotifierClient::class);
        $client->expects($this->once())
            ->method('requestNotication')
            ->with('user-2')
            ->willThrowException(new GatewayTimeoutException('timeout'));

        $notifier = new Notifier($client);

        $this->expectException(GatewayTimeoutException::class);
        $notifier->notify('user-2');
    }
}
