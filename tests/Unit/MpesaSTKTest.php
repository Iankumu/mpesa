<?php

namespace Iankumu\Mpesa\Tests\Unit;

use Iankumu\Mpesa\Exceptions\CallbackException;
use Iankumu\Mpesa\Mpesa;
use Illuminate\Support\Facades\Http;

it('can initiate stkpush', function () {

    $expectedResponse = [
        'MerchantRequestID' => '29115-34620561-1',
        'CheckoutRequestID' => 'ws_CO_191220191020363925',
        'ResponseCode' => '0',
        'ResponseDescription' => 'Success. Request accepted for processing',
        'CustomerMessage' => 'Success. Request accepted for processing',
    ];

    Http::fake([
        'https://sandbox.safaricom.co.ke/*' => Http::response($expectedResponse),
    ]);

    $mpesa = new Mpesa();

    $response = $mpesa->stkpush('0707070707', 100, 12345, 'https://test.test/callback');


    // $result = json_decode($response->body(), true);
    $result = $response->json();

    expect($response->status())->toBe(200);
    expect($result)->toBe($expectedResponse);
    expect($result['ResponseCode'])->toBe('0');
});

it('can return stk query response', function () {

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'The service request has been accepted successfully',
        'MerchantRequestID' => '22205-34066-1',
        'CheckoutRequestID' => 'ws_CO_13012021093521236557',
        'ResultCode' => '0',
        'ResultDesc' => 'The service request is processed successfully.',
    ];

    Http::fake([
        'https://sandbox.safaricom.co.ke/*' => Http::response($expectedResponse),
    ]);

    $mpesa = new Mpesa();

    $response = $mpesa->stkquery('ws_CO_191220191020363925');

    $result = json_decode($response->body(), true);

    expect($response->status())->toBe(200);
    expect($result)->toBe($expectedResponse);
    expect($result['ResponseCode'])->toBe('0');
});

test('that stkpush will throw an exception when the callbacks are null', function () {

    (new Mpesa())->stkpush('0707070707', 100, 12345);
})->expectException(CallbackException::class);
