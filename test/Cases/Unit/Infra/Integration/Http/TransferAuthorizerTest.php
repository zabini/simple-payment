<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Infra\Integration\Http;

use App\Core\Domain\Exceptions\InvalidOperation;
use App\Infra\Integration\Http\Client\Authorizer;
use App\Infra\Integration\Http\TransferAuthorizer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Infra\Integration\Http\TransferAuthorizer
 * @internal
 */
class TransferAuthorizerTest extends TestCase
{
    public function testAuthorizePassesThroughWhenExternalApproves(): void
    {
        $client = $this->createMock(Authorizer::class);
        $client->expects($this->once())
            ->method('requestAuthorization')
            ->with('user-1')
            ->willReturn(true);

        $authorizer = new TransferAuthorizer($client);

        $authorizer->authorize('user-1');
        $this->assertTrue(true); // reached without exception
    }

    public function testAuthorizeThrowsWhenExternalDenies(): void
    {
        $client = $this->createMock(Authorizer::class);
        $client->expects($this->once())
            ->method('requestAuthorization')
            ->with('user-1')
            ->willReturn(false);

        $authorizer = new TransferAuthorizer($client);

        $this->expectException(InvalidOperation::class);
        $this->expectExceptionMessage('Authorization failed');

        $authorizer->authorize('user-1');
    }
}
