<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Infra\Http\Request\Transfer;

use App\Infra\Http\Request\Transfer\Create;
use Hyperf\Contract\ContainerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Infra\Http\Request\Transfer\Create
 * @internal
 */
class CreateRequestTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testAuthorizeAlwaysReturnsTrue(): void
    {
        $request = new Create(Mockery::mock(ContainerInterface::class));

        $this->assertTrue($request->authorize());
    }

    public function testRules(): void
    {
        $request = new Create(Mockery::mock(ContainerInterface::class));

        $this->assertSame([
            'payer' => 'required|string',
            'payee' => 'required|string',
            'amount' => 'required|numeric',
        ], $request->rules());
    }

    public function testMessages(): void
    {
        $request = new Create(Mockery::mock(ContainerInterface::class));

        $this->assertSame([
            'payer.required' => 'Payer is required.',
            'payer.string' => 'Payer must be a string.',
            'payee.required' => 'Payee is required.',
            'payee.string' => 'Payee must be a string.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
        ], $request->messages());
    }
}
