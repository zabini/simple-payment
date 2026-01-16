<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Infra\Integration\Http\Response;

use App\Core\Domain\Exceptions\NotificationException;
use App\Infra\Exception\GatewayTimeoutException;
use App\Infra\Integration\Http\Response\NotifierResponse;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \App\Infra\Integration\Http\Concerns\ResponseHandler
 * @covers \App\Infra\Integration\Http\Response\NotifierResponse
 * @internal
 */
class NotifierResponseTest extends TestCase
{
    public function testRequestExceptionIsMappedToGatewayTimeout(): void
    {
        $exception = new RequestException('timeout', new Request('POST', '/api/v1/notify'));

        $this->expectException(GatewayTimeoutException::class);

        NotifierResponse::failure($exception);
    }

    public function testGenericExceptionIsWrappedAsNotificationException(): void
    {
        $exception = new RuntimeException('boom');

        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Failed to notify payee');

        NotifierResponse::failure($exception);
    }
}
