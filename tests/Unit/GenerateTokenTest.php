<?php

namespace Iankumu\Mpesa\Tests\Unit;

use Iankumu\Mpesa\Mpesa;
use Illuminate\Support\Facades\Http;

it('can generate token', function () {

    $expectedResponse = [
        'access_token' => 'Test Token',
        'expires_in' => '3599',
    ];

    Http::fake([
        'https://sandbox.safaricom.co.ke/*' => Http::response($expectedResponse),
    ]);

    $response = (new Mpesa())->generateAccessToken();

    expect($response)->toBe('Test Token');
});
