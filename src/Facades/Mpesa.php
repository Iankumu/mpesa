<?php

namespace Iankumu\Mpesa\Facades;

use Illuminate\Support\Facades\Facade;



/**
 * @method static \Illuminate\Http\Client\Response stkpush(int $phonenumber, int $amount, string|null $account_number, string|null $callbackurl = null, string $transactionType)
 * @method static \Illuminate\Http\Client\Response stkquery(string $checkoutRequestId)
 * @method static \Illuminate\Http\Client\Response b2c(int $phonenumber, string $command_id, int $amount, string $remarks, string|null $result_url = null, string|null $timeout_url = null)
 * @method static \Illuminate\Http\Client\Response b2b(int $receiver_shortcode, string $command_id, int $amount, string $remarks, string|null $account_number = null, string|null $b2b_result_url = null, string|null $b2b_timeout_url = null)
 * @method static \Illuminate\Http\Client\Response validated_b2c(int $phonenumber, string $command_id, int $amount, string $remarks, string $id_number, string|null $result_url = null, string|null $timeout_url = null)
 * @method static \Illuminate\Http\Client\Response c2bregisterURLS(string $shortcode, string|null $confirmurl = null, string|null $validateurl = null)
 * @method static \Illuminate\Http\Client\Response c2bsimulate(int $phonenumber, int $amount, string $shortcode, string $command_id, string|null $account_number = null)
 * @method static \Illuminate\Http\Client\Response transactionStatus(int $shortcode, string $transactionid, int $identiertype, string $remarks, string|null $result_url = null, string|null $timeout_url = null)
 * @method static \Illuminate\Http\Client\Response accountBalance(int $shortcode, int $identiertype, string $remarks, string|null $result_url = null, string|null $timeout_url = null)
 * @method static \Illuminate\Http\Client\Response reversal(int $shortcode, string $transactionid, int $amount, string $remarks, string|null $reverseresulturl = null, string|null $reversetimeouturl = null)
 * @method static \Illuminate\Http\Response validationResponse(int $result_code, string $result_description)
 *
 * @see MpesaGateway
 */
class Mpesa extends Facade
{
    public const PAYBILL = \Iankumu\Mpesa\Mpesa::PAYBILL;
    public const TILL    = \Iankumu\Mpesa\Mpesa::TILL;

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'iankumu-mpesa';
    }
}
