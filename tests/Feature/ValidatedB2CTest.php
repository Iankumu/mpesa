<?php

namespace Iankumu\Mpesa\Tests\Feature;

use Iankumu\Mpesa\Exceptions\CallbackException;
use Iankumu\Mpesa\Facades\Mpesa;
use Illuminate\Support\Facades\Http;

it('can initiate validated_b2c with callbacks passed as parameters', function () {

    $expectedResponse = [

        'ConversationID' => 'AG_20190117_00004636fb3ac56655df',
        'OriginatorConversationID' => '17503-13504109-1',
        'ResponseCode' => '0',
        'ResponseDescription' => 'Accept the service request successfully.',
    ];

    Http::fake([
        'https://sandbox.safaricom.co.ke/*' => Http::response($expectedResponse),
    ]);

    // config()->set('mpesa.b2c_result_url', 'http://test.test/result');
    // config()->set('mpesa.b2c_timeout_url', 'http://test.test/timeout');



    $response = Mpesa::validated_b2c(
        '0707070707',
        'SalaryPayment',
        100,
        'Salary Payment',
        '120912992',
        'http://test.test/result',
        'http://test.test/timeout'
    );

    // $result = json_decode($response->body(), true);
    $result = $response->json();

    expect($response->status())->toBe(200);
    expect($result)->toBe($expectedResponse);
    expect($result['ResponseCode'])->toBe('0');
});

it('can initiate validated_b2c with callbacks set as configurations', function () {

    $expectedResponse = [

        'ConversationID' => 'AG_20190117_00004636fb3ac56655df',
        'OriginatorConversationID' => '17503-13504109-1',
        'ResponseCode' => '0',
        'ResponseDescription' => 'Accept the service request successfully.',
    ];

    Http::fake([
        'https://sandbox.safaricom.co.ke/*' => Http::response($expectedResponse),
    ]);

    config()->set('mpesa.callbacks.b2c_result_url', 'http://test.test/result');
    config()->set('mpesa.callbacks.b2c_timeout_url', 'http://test.test/timeout');


    $response = Mpesa::validated_b2c(
        '0707070707',
        'SalaryPayment',
        100,
        'Salary Payment',
        '120912992'
    );

    // $result = json_decode($response->body(), true);
    $result = $response->json();

    expect($response->status())->toBe(200);
    expect($result)->toBe($expectedResponse);
    expect($result['ResponseCode'])->toBe('0');
});

test('that validated_b2c will throw an exception when the callbacks are null', function () {

    Mpesa::validated_b2c(
        '0707070707',
        'SalaryPayment',
        100,
        'Salary Payment',
        '120912992',
        null,
        null
    );
})->expectException(CallbackException::class);
