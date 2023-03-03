<?php

namespace Iankumu\Mpesa\Tests\Unit;

use Iankumu\Mpesa\Exceptions\CallbackException;
use Iankumu\Mpesa\Mpesa;
use Iankumu\Mpesa\Tests\TestCase;

class MpesaSTKTest extends TestCase
{
    /** @test */
    public function can_initiate_stkpush()
    {
        $mpesaStub = $this->createStub(Mpesa::class);

        $mpesaStub->method('stkpush')->willReturn(true);

        $result = $mpesaStub->stkpush('0707070707', 100, 12345, 'https://test.test/callback');

        $this->assertSame(true, $result);
    }

    /** @test */
    public function stkpush_will_throw_an_exception_when_the_callbacks_are_null()
    {
        $this->expectException(CallbackException::class);

        //Should Throw an Exception as the callback is null
        (new Mpesa())->stkpush('0707070707', 100, 12345);
    }
}
