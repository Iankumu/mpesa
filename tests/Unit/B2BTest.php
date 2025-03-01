<?php

namespace Iankumu\Mpesa\Tests\Unit;

use Iankumu\Mpesa\Exceptions\CallbackException;
use Iankumu\Mpesa\Mpesa;
use Illuminate\Support\Facades\Http;

test('that b2b will throw an exception when the callbacks are null', function () {
    (new Mpesa())->b2b('403043', 'BusinessPayBill', 100, 'test', 'test');
})->expectException(CallbackException::class);

it('can initiate b2b with callbacks passed as parameters', function () {

    Http::fake([
        'https://sandbox.safaricom.co.ke/mpesa/b2b/v1/paymentrequest' => Http::response([
            'ResponseCode' => '0',
            'ResponseDescription' => 'Success',
            'ConversationID' => 'AG_20200708_00008d7b7b7b7b7b7b7b',
            'OriginatorConversationID' => '12345-67890-2',
            'TransactionID' => 'LGR019GK1W',
        ], 200),
    ]);

    $mpesa = new Mpesa();
    $response = $mpesa->b2b('403043', 'BusinessPayBill', 100, 'test', 'test', 'http://test.test/result', 'http://test.test/timeout');

    // $result = json_decode($response->body(), true);
    $result = $response->json();

    expect($response->status())->toBe(200);
    expect($result)->toHaveKeys(['ResponseCode', 'ResponseDescription', 'ConversationID', 'OriginatorConversationID', 'TransactionID']);
    expect($result['ResponseCode'])->toBe('0');
});

it('can initiate b2b with callbacks set as configurations', function () {

    Http::fake([
        'https://sandbox.safaricom.co.ke/mpesa/b2b/v1/paymentrequest' => Http::response([
            'ResponseCode' => '0',
            'ResponseDescription' => 'Success',
            'ConversationID' => 'AG_20200708_00008d7b7b7b7b7b7b7b',
            'OriginatorConversationID' => '12345-67890-2',
            'TransactionID' => 'LGR019GK1W',
        ], 200),
    ]);

    config()->set('mpesa.callbacks.b2b_result_url', 'http://test.test/result');
    config()->set('mpesa.callbacks.b2b_timeout_url', 'http://test.test/timeout');

    $mpesa = new Mpesa();
    $response = $mpesa->b2b('403043', 'BusinessPayBill', 100, 'test', 'test');

    // $result = json_decode($response->body(), true);
    $result = $response->json();

    expect($response->status())->toBe(200);
    expect($result)->toHaveKeys(['ResponseCode', 'ResponseDescription', 'ConversationID', 'OriginatorConversationID', 'TransactionID']);
    expect($result['ResponseCode'])->toBe('0');
});
