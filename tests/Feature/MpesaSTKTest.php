<?php

namespace Iankumu\Mpesa\Tests\Feature;

use Iankumu\Mpesa\Exceptions\CallbackException;
use Illuminate\Support\Facades\Http;
use Iankumu\Mpesa\Facades\Mpesa;

it('can initiate stkpush with callbacks passed as parameters', function () {

    $expectedResponse = [
        'MerchantRequestID' => '29115-34620561-1',
        'CheckoutRequestID' => 'ws_CO_191220191020363925',
        'ResponseCode' => '0',
        'ResponseDescription' => 'Success. Request accepted for processing',
        'CustomerMessage' => 'Success. Request accepted for processing',
    ];

    Http::fake([
        'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest' => Http::response($expectedResponse),
    ]);

    $response = Mpesa::stkpush(
        '0707070707',
        100,
        12345,
        'https://test.test/callback',
        Mpesa::PAYBILL
    );

    // $result = json_decode($response->body(), true);
    $result = $response->json();

    expect($response->status())->toBe(200);
    expect($result)->toBe($expectedResponse);
    expect($result['ResponseCode'])->toBe('0');
});

it('can initiate stkpush with callbacks set as configurations', function () {

    $expectedResponse = [
        'MerchantRequestID' => '29115-34620561-1',
        'CheckoutRequestID' => 'ws_CO_191220191020363925',
        'ResponseCode' => '0',
        'ResponseDescription' => 'Success. Request accepted for processing',
        'CustomerMessage' => 'Success. Request accepted for processing',
    ];

    Http::fake([
        'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest' => Http::response($expectedResponse),
    ]);

    config()->set('mpesa.callbacks.callback_url', 'https://test.test/callback');



    $response = Mpesa::stkpush(
        '0707070707',
        100,
        12345,
        null,
        Mpesa::PAYBILL
    );

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


    $response = Mpesa::stkquery('ws_CO_191220191020363925');

    $result = json_decode($response->body(), true);

    expect($response->status())->toBe(200);
    expect($result)->toBe($expectedResponse);
    expect($result['ResponseCode'])->toBe('0');
});

test('that stkpush will throw an exception when the callbacks are null', function () {

    Mpesa::stkpush('0707070707', 100, 12345, null);
})->expectException(CallbackException::class);


it('can initiate stkpush for till numbers', function () {
    $expectedResponse = [
        'MerchantRequestID'   => '29115-34620561-1',
        'CheckoutRequestID'   => 'ws_CO_191220191020363925',
        'ResponseCode'        => '0',
        'ResponseDescription' => 'Success. Request accepted for processing',
        'CustomerMessage'     => 'Success. Request accepted for processing',
    ];

    // 1. Fake the HTTP endpoint
    Http::fake([
        'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
        => Http::response($expectedResponse, 200),
    ]);

    /** @var \Illuminate\Http\Client\Response $response */
    $response = Mpesa::stkpush(
        '0707070707',
        100,
        null,
        'https://test.test/callback',
        Mpesa::TILL
    );

    $result = json_decode($response->body(), true);

    expect($response->json())->toEqual($expectedResponse);

    expect($response->status())->toBe(200);
    expect($result)->toBe($expectedResponse);
    expect($result['ResponseCode'])->toBe('0');
});
