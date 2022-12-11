<?php

namespace Iankumu\Mpesa\Tests\Unit;

use Iankumu\Mpesa\Mpesa;
use Iankumu\Mpesa\Tests\TestCase;

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
}
