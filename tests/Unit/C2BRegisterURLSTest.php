<?php

namespace Iankumu\Mpesa\Tests\Unit;

use Iankumu\Mpesa\Mpesa;
use Iankumu\Mpesa\Tests\TestCase;

class C2BRegisterURLSTest extends TestCase
{
    /** @test */
    public function can_register_c2b_urls()
    {
        $mpesa = $this->createStub(Mpesa::class);

        $mpesa->method('c2bregisterURLS')
            ->with(12345)
            ->willReturn(true);

        $this->assertSame(true, $mpesa->c2bregisterURLS(12345));
    }
}
