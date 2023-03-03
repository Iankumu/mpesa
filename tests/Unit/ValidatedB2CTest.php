<?php

namespace Iankumu\Mpesa\Tests\Unit;

use Iankumu\Mpesa\Exceptions\CallbackException;
use Iankumu\Mpesa\Mpesa;
use Iankumu\Mpesa\Tests\TestCase;
use Illuminate\Support\Facades\Log;

class ValidatedB2CTest extends TestCase
{
    //test can perform validated b2c
    /**@test */
    public function can_validate_b2c()
    {
        $mpesa = $this->createStub(Mpesa::class);

        $mpesa->method('validated_b2c')
            ->with('0707070707', 'SalaryPayment', 100, 'Salary Payment', '120912992') //Will take a phone number and ID Number of the person to be paid
            ->willReturn(true);

        $result = $mpesa->validated_b2c('0707070707', 'SalaryPayment', 100, 'Salary Payment', '120912992');
        $result->assertJsonStructure([
            'ConversationID',
            'OriginatorConversationID',
            'ResponseCode',
            'ResponseDescription',
        ]);
    }

    /** @test */
    public function validated_b2c_will_throw_an_exception_when_the_callbacks_are_null()
    {
        $this->expectException(CallbackException::class);

        //Should Throw an Exception as the callback is null
        (new Mpesa())->validated_b2c('0707070707', 'SalaryPayment', 100, 'Salary Payment', '120912992');;
    }
}
