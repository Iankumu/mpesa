<?php

namespace Iankumu\Mpesa\Facades;

use Illuminate\Support\Facades\Facade;

class Mpesa extends Facade
{
    /**
     * @method static stkpush($phonenumber, $amount, $account_number,$callbackurl = null);
     * @method static stkquery($checkoutRequestId);
     * @method static b2c($phonenumber, $command_id, $amount, $remarks);
     * @method static c2bregisterURLS($shortcode);
     * @method static c2bsimulate($phonenumber, $amount, $shortcode, $command_id, $account_number = NULL);
     * @method static transactionStatus($shortcode, $transactionid, $identiertype, $remarks);
     * @method static accountBalance($shortcode, $identiertype, $remarks);
     * @method static reversal($shortcode, $transactionid, $amount, $remarks);
     */
    protected static function getFacadeAccessor()
    {
        return 'iankumu-mpesa';
    }
}
