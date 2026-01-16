<?php

declare(strict_types=1);

namespace HyperfTest\Cases\Unit\Infra\Http\Request\User;

use App\Infra\Http\Request\User\Create;
use Hyperf\Contract\ContainerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Infra\Http\Request\User\Create
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
            'full_name' => 'required|string',
            'kind' => 'required|string',
            'document_type' => 'required|string',
            'document' => 'required|string',
            'email' => 'required|string',
            'password' => 'required|string',
        ], $request->rules());
    }

    public function testMessages(): void
    {
        $request = new Create(Mockery::mock(ContainerInterface::class));

        $this->assertSame([
            'full_name.required' => 'Full name is required.',
            'full_name.string' => 'Full name must be a string.',
            'kind.required' => 'Kind is required.',
            'kind.string' => 'Kind must be a string.',
            'document_type.required' => 'Document type is required.',
            'document_type.string' => 'Document type must be a string.',
            'document.required' => 'Document is required.',
            'document.string' => 'Document must be a string.',
            'email.required' => 'email is required.',
            'email.string' => 'email must be a string.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
        ], $request->messages());
    }
}
