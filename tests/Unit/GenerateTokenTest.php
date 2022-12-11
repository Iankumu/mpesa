<?php

namespace Iankumu\Mpesa\Tests\Unit;

use Iankumu\Mpesa\Mpesa;
use Iankumu\Mpesa\Tests\TestCase;

class GenerateTokenTest extends TestCase
{


    /** @test */
    public function can_generate_token()
    {

        $mpesaStub = $this->createStub(Mpesa::class);

        $mpesaStub->method('generateAccessToken')->willReturn(true);


        $result = $mpesaStub->generateAccessToken();

        $this->assertSame(true, $result);
    }
}
