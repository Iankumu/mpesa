<?php

namespace Iankumu\Mpesa\Facades;

use Iankumu\Mpesa\Mpesa as MpesaGateway;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Iankumu\Mpesa\Mpesa stkpush($phonenumber, $amount, $account_number,$callbackurl = null)
 * @method static \Iankumu\Mpesa\Mpesa stkquery($checkoutRequestId)
 * @method static \Iankumu\Mpesa\Mpesa b2c($phonenumber, $command_id, $amount, $remarks)
 * @method static \Iankumu\Mpesa\Mpesa b2b($receiver_shortcode, $command_id, $amount, $remarks, $account_number=null)
 * @method static \Iankumu\Mpesa\Mpesa validated_b2c($phonenumber, $command_id, $amount, $remarks,$id_number)
 * @method static \Iankumu\Mpesa\Mpesa c2bregisterURLS($shortcode)
 * @method static \Iankumu\Mpesa\Mpesa c2bsimulate($phonenumber, $amount, $shortcode, $command_id, $account_number = null)
 * @method static \Iankumu\Mpesa\Mpesa transactionStatus($shortcode, $transactionid, $identiertype, $remarks)
 * @method static \Iankumu\Mpesa\Mpesa accountBalance($shortcode, $identiertype, $remarks)
 * @method static \Iankumu\Mpesa\Mpesa reversal($shortcode, $transactionid, $amount, $remarks)
 * @method static \Iankumu\Mpesa\Mpesa validationResponse($result_code, $result_description)
 *
 * @see MpesaGateway
 */
class Mpesa extends Facade
{
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
