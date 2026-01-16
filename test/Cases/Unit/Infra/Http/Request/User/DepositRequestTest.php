<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Infra\Http\Request\User;

use App\Infra\Http\Request\User\Deposit;
use Hyperf\Contract\ContainerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Infra\Http\Request\User\Deposit
 * @internal
 */
class DepositRequestTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testAuthorizeAlwaysReturnsTrue(): void
    {
        $request = new Deposit(Mockery::mock(ContainerInterface::class));

        $this->assertTrue($request->authorize());
    }

    public function testRules(): void
    {
        $request = new Deposit(Mockery::mock(ContainerInterface::class));

        $this->assertSame([
            'amount' => 'required|numeric',
        ], $request->rules());
    }

    public function testMessages(): void
    {
        $request = new Deposit(Mockery::mock(ContainerInterface::class));

        $this->assertSame([
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
        ], $request->messages());
    }
}
