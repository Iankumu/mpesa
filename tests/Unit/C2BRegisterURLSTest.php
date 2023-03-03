<?php

namespace Iankumu\Mpesa\Tests\Unit;

use Iankumu\Mpesa\Exceptions\CallbackException;
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

    /** @test */
    public function c2b_will_throw_an_exception_when_the_callbacks_are_null()
    {
        $this->expectException(CallbackException::class);

        //Should Throw an Exception as the callback is null
        (new Mpesa())->c2bregisterURLS(12345);
    }
}
