<?php

namespace Iankumu\Mpesa\Tests\Feature;

use Iankumu\Mpesa\Exceptions\CallbackException;
use Iankumu\Mpesa\Facades\Mpesa;
use Illuminate\Support\Facades\Http;

it('can initiate b2c with callbacks passed as parameters', function () {

    $expectedResponse = [
        'ConversationID' => 'AG_20191219_00005797af5d7d75f652',
        'OriginatorConversationID' => '16740-34861180-1',
        'ResponseCode' => '0',
        'ResponseDescription' => 'Accept the service request successfully.',
    ];

    Http::fake([
        'https://sandbox.safaricom.co.ke/*' => Http::response($expectedResponse),
    ]);

    $response = Mpesa::b2c(
        '0707070707',
        'SalaryPayment',
        100,
        'Salary Payment',
        'http://test.test/result',
        'http://test.test/timeout'
    );

    // $result = json_decode($response->body(), true);

    $result = $response->json();

    expect($response->status())->toBe(200);
    expect($result)->toBe($expectedResponse);
    expect($result['ResponseCode'])->toBe('0');
});

it('can initiate b2c with callbacks set as configurations', function () {

    $expectedResponse = [
        'ConversationID' => 'AG_20191219_00005797af5d7d75f652',
        'OriginatorConversationID' => '16740-34861180-1',
        'ResponseCode' => '0',
        'ResponseDescription' => 'Accept the service request successfully.',
    ];

    config()->set('mpesa.callbacks.b2c_result_url', 'http://test.test/result');
    config()->set('mpesa.callbacks.b2c_timeout_url', 'http://test.test/timeout');

    Http::fake([
        'https://sandbox.safaricom.co.ke/*' => Http::response($expectedResponse),
    ]);

    $response = Mpesa::b2c(
        '0707070707',
        'SalaryPayment',
        100,
        'Salary Payment'
    );

    // $result = json_decode($response->body(), true);

    $result = $response->json();

    expect($response->status())->toBe(200);
    expect($result)->toBe($expectedResponse);
    expect($result['ResponseCode'])->toBe('0');
});

test('that b2c will throw an exception when the callbacks are null', function () {

    Mpesa::b2c(
        '0707070707',
        'SalaryPayment',
        100,
        'Salary Payment',
        null,
        null
    );
})->expectException(CallbackException::class);
