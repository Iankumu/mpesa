<?php

namespace Iankumu\Mpesa\Tests\Unit;

use Iankumu\Mpesa\Mpesa;
use Iankumu\Mpesa\Tests\TestCase;
use Iankumu\Mpesa\Utils\MpesaHelper;

class GenerateSecurityCredentialTest extends TestCase
{


    /** @test */
    public function can_generate_security_credential()
    {

        $mpesa = new Mpesa();

        $result = $mpesa->generate_security_credential();
        $this->assertStringContainsString('==', $result);
    }
}
