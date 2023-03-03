<?php

namespace Iankumu\Mpesa\Tests\Unit;

use Iankumu\Mpesa\Mpesa;
use Iankumu\Mpesa\Tests\TestCase;
use Iankumu\Mpesa\Exceptions\CallbackException;

class B2CTest extends TestCase
{
    /**@test */
    public function can_initiate_b2c()
    {
        $mpesa = $this->createStub(Mpesa::class);

        $mpesa->method('b2c')
            ->with('0707070707', 'SalaryPayment', 100, 'Salary Payment')
            ->willReturn(true);

        $result = $mpesa->b2c('0707070707', 'SalaryPayment', 100, 'Salary Payment');
        $this->assertSame(true, $result);
    }

    /** @test */
    public function b2c_will_throw_an_exception_when_the_callbacks_are_null()
    {
        $this->expectException(CallbackException::class);

        //Should Throw an Exception as the callback is null
        (new Mpesa())->b2c('0707070707', 'SalaryPayment', 100, 'Salary Payment');
    }
}
