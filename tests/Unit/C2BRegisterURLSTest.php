<?php

namespace Iankumu\Mpesa\Tests\Unit;

use Iankumu\Mpesa\Exceptions\CallbackException;
use Iankumu\Mpesa\Mpesa;
use Illuminate\Support\Facades\Http;

it('can register c2b urls', function () {

    $expectedResponse = [
        'ConversationID' => 'AG_20191219_00005797af5d7d75f652',
        'OriginatorConversationID' => '16740-34861180-1',
        'ResponseDescription' => 'success',
    ];

    config()->set('mpesa.c2b_validation_url', 'http://test.test/validation');
    config()->set('mpesa.c2b_confirmation_url', 'http://test.test/confrim');

    Http::fake([
        'https://sandbox.safaricom.co.ke/*' => Http::response($expectedResponse),
    ]);

    $mpesa = new Mpesa();

    $response = $mpesa->c2bregisterURLS(12345);

    // $result = json_decode($response->body(), true);
    $result = $response->json();

    expect($response->status())->toBe(200);
    expect($result)->toBe($expectedResponse);
    expect($result['ResponseDescription'])->toBe('success');
});

test('that c2b will throw an exception when the callbacks are null', function () {

    (new Mpesa())->c2bregisterURLS(12345);
})->expectException(CallbackException::class);
