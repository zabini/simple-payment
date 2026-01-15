<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use Hyperf\Testing\TestCase;

/**
 * @covers \App\Infra\Http\Controller\IndexController
 * @internal
 */
class IndexTest extends TestCase
{
    public function testExample()
    {
        $this->get('/')->assertOk()->assertSee('It Works');
    }
}
