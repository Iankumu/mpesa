<?php

namespace Iankumu\Mpesa\Tests\Unit;

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
}
