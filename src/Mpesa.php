<?php

namespace Iankumu\Mpesa;

use Iankumu\Mpesa\Utils\MpesaHelper;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class Mpesa
{
    use MpesaHelper;

    /**
     * The encrypted API credentials
     * @var string $security_credential
     */
    private $security_credential;


    /**
     * The Lipa Na MPesa shortcode
     * @var int $shortcode
     */
    public $shortcode;

    /**
     * The Mpesa B2C shortcode
     * @var int $b2c_shortcode
     */
    public $b2c_shortcode;


    /**
     * The Mpesa portal Username
     * @var string $initiator_name
     */
    public $initiator_name;

    /**
     * The Mpesa Environment
     * @var string $env
     */
    public $env;


    /*Callbacks*/
    public $b2ctimeout;
    public $b2cresult;
    public $baltimeout;
    public $balresult;
    public $statustimeout;
    public $statusresult;
    public $reversetimeout;
    public $reverseresult;
    public $c2bvalidate;
    public $c2bconfirm;
    public $stkcallback;

    /**
     * The Base URL
     * @var string $url
     */
    public $url;


    /**
     * Construct method
     *
     * Initializes the class with API values.
     *
     */

    public function __construct()
    {
        $this->security_credential = $this->generate_security_credential();
        $this->env = config('mpesa.environment');
        $this->shortcode = config('mpesa.shortcode');
        $this->stkcallback = config('mpesa.callback_url');
        $this->b2c_shortcode = config('mpesa.b2c_shortcode');
        $this->b2ctimeout = config('mpesa.b2c_timeout_url');
        $this->b2cresult = config('mpesa.b2c_result_url');
        $this->c2bconfirm = config('mpesa.c2b_confirmation_url');
        $this->c2bvalidate = config('mpesa.c2b_validation_url');
        $this->initiator_name = config('mpesa.initiator_name');
        $this->statusresult = config('mpesa.status_result_url');
        $this->statustimeout = config('mpesa.status_timeout_url');
        $this->balresult = config('mpesa.balance_result_url');
        $this->baltimeout = config('mpesa.balance_timeout_url');
        $this->reverseresult = config('mpesa.reversal_result_url');
        $this->reversetimeout = config('mpesa.reversal_timeout_url');
        $this->url = config('mpesa.environment') == 'sandbox'
            ? "https://sandbox.safaricom.co.ke"
            : "https://api.safaricom.co.ke";
    }

    /**
     * Mpesa STKPUSH
     *
     * This method is used to initiate an online payment on behalf of a customer
     *
     * @param int $phonenumber The phone number that will receive the stkpush prompt in the format 254xxxxxxxxx
     * @param int $amount The amount to be transacted
     * @param string $account_number The account number for a paybill
     * @return object Curl Response from Mpesa
     */
    public function stkpush($phonenumber, $amount, $account_number, $callbackurl = null)
    {
        $url = $this->url . "/mpesa/stkpush/v1/processrequest";
        $curl_post_data = [
            //Fill in the request parameters with valid values
            'BusinessShortCode' => $this->shortcode, //Has to be a paybill and not a till number since it is not supported
            'Password' => $this->lipaNaMpesaPassword(),
            'Timestamp' => Carbon::rawParse('now')->format('YmdHms'),
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int)$amount,
            'PartyA' => $this->phoneValidator($phonenumber), // replace this with your phone number
            'PartyB' =>  $this->shortcode,
            'PhoneNumber' => $this->phoneValidator($phonenumber), // replace this with your phone number
            'AccountReference' => $account_number, //Account Number for a paybill..Maximum of 12 Characters.
            'TransactionDesc' => "Payment" //Maximum of 13 Characters.
        ];

        //url should be https and should not contain keywords such as mpesa,safaricom etc
        if (!is_null($callbackurl) && is_null($this->stkcallback)) {
            $curl_post_data += [
                'CallBackURL' => $callbackurl
            ];
        } elseif (is_null($callbackurl) && !is_null($this->stkcallback)) {
            $curl_post_data += [
                'CallBackURL' => $this->stkcallback
            ];
        } elseif (!is_null($callbackurl) && !is_null($this->stkcallback)) {
            $curl_post_data += [
                'CallBackURL' => $callbackurl
            ];
        } else {
            return response()->json([
                'error' => 'Callback URL cannot be null'
            ], Response::HTTP_NOT_ACCEPTABLE);
        }

        $response = $this->MpesaRequest($url, $curl_post_data);
        return $response;
    }

    /**
     * Mpesa STKPUSH Query
     *
     * This method is used to check the status of a Lipa Na M-Pesa Online Payment.
     *
     * @param string $checkoutRequestId This is a global unique identifier of the processed checkout transaction request.
     * @return object Curl Response from Mpesa
     */

    public function stkquery($checkoutRequestId)
    {
        $post_data = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $this->lipaNaMpesaPassword(),
            'Timestamp' => Carbon::rawParse('now')->format('YmdHms'),
            'CheckoutRequestID' => $checkoutRequestId
        ];

        $url = $this->url . "/mpesa/stkpushquery/v1/query";

        $response = $this->MpesaRequest($url, $post_data);
        return $response;
    }


    /**
     * Business to Client
     *
     * This method is used to send money to a client's Mpesa account.
     *
     * @param int $amount The amount to send to the recipient
     * @param int $phonenumber The phone number of the recipient in the format 254xxxxxxxxx
     * @param string $command_id The type of transaction being made. Can be SalaryPayment,BusinessPayment or PromotionPayment
     * @param string $remarks Any additional information. Must be present.
     * @return object Curl Response from Mpesa
     */
    public function b2c($phonenumber, $command_id, $amount, $remarks)
    {
        $url = $this->url . "/mpesa/b2c/v1/paymentrequest";

        $body = [
            "InitiatorName" => $this->initiator_name,
            "SecurityCredential" => $this->security_credential,
            "CommandID" => $command_id, //can be SalaryPayment,BusinessPayment or PromotionPayment
            "Amount" => $amount,
            "PartyA" => $this->b2c_shortcode,
            "PartyB" => $this->phoneValidator($phonenumber),
            "Remarks" => $remarks,
            "QueueTimeOutURL" => $this->b2ctimeout,
            "ResultURL" => $this->b2cresult,
            "Occassion" => '', //can be null
        ];

        $response = $this->MpesaRequest($url, $body);
        return $response;
    }
    /**
     * Business to Client With Validation
     *
     * This method is used to send money to a client's Mpesa account.
     * It requires one to provide the id number of the recipient, and fails if the id number does not match the phone number.
     *COMMON ERRORS
     * 1. Duplicate Originator Conversation ID. -> This means you have already sent a request with the same OriginatorConversationID
     * 2. Invalid Access    Token - Invalid API call as no apiproduct match found”. -> The Daraja sandbox/ production app
    you are using to run the tests does not
    have the B2C with validation product.
    Send an email to
    apisupport@safaricom.co.ke requesting
    for addition of the product to your
    sandbox app. Specify your
    prod/sandbox app to which the product
    should be added.
     * @param int $amount The amount to send to the recipient
     * @param int $phonenumber The phone number of the recipient in the format 254xxxxxxxxx
     * @param string $command_id The type of transaction being made. Can be SalaryPayment,BusinessPayment or PromotionPayment
     * @param string $remarks Any additional information. Must be present.
     * @return object Curl Response from Mpesa
     */
    public function validated_b2c($phonenumber, $command_id, $amount, $remarks,$id_number)
    {
        $url = $this->url . "/mpesa/b2cvalidate/v2/paymentrequest";

        //get first two digits of the id number
        $id_type = substr($id_number, 0, 2);
        //the rest is the id number
        $id_number = substr($id_number, 2);

        $body = [
            "InitiatorName" => $this->initiator_name,
            "SecurityCredential" => $this->security_credential,
            "CommandID" => $command_id, //can be SalaryPayment,BusinessPayment or PromotionPayment
            "Amount" => $amount,
            "PartyA" => $this->b2c_shortcode,
            "PartyB" => $this->phoneValidator($phonenumber),
            "Remarks" => $remarks,
            "QueueTimeOutURL" => $this->b2ctimeout,
            "ResultURL" => $this->b2cresult,
            "Occassion" => '', //can be null
            "OriginatorConversationID" => Carbon::rawParse('now')->format('YmdHms'),//unique id for the transaction
            "IDType" => $id_type, //First two digits of the id number
            "IDNumber" => $id_number,
        ];

        $response = $this->MpesaRequest($url, $body);
        return $response;
    }

    /**
     * Client to Business
     *
     * This method is used to register URLs for callbacks when money is sent from the MPesa toolkit menu
     *
     * @param string $shortcode The till number or paybill number the urls will be associated with
     * @return object Curl Response from Mpesa
     */
    public function c2bregisterURLS($shortcode)
    {

        $url = $this->url . "/mpesa/c2b/v2/registerurl";

        $body = [
            "ShortCode" => $shortcode,
            "ResponseType" => 'Completed', //Completed or Cancelled
            "ConfirmationURL" => $this->c2bconfirm, //url should be https and should not contain keywords such as mpesa,safaricom etc
            "ValidationURL" => $this->c2bvalidate, //url should be https and should not contain keywords such as mpesa,safaricom etc
        ];

        $response = $this->MpesaRequest($url, $body);
        return $response;
    }

    /**
     * C2B Simulation
     *
     * This method is used to simulate a C2B Transaction to test your ConfirmURL and ValidationURL in the Client to Business method
     *
     * @param int $amount The amount to send to shortcode
     * @param int $phonenumber A dummy Safaricom phone number to simulate transaction in the format 254xxxxxxxxx
     * @param string $shortcode The Paybill/Till number receiving the funds
     * @param string $command_id The Type of transaction. Whether it is a paybill transaction(CustomerPayBillOnline) or a Till number transaction(CustomerBuyGoodsOnline)
     * @param string $account_number The account number for a paybill. The default is null
     * @return object Curl Response from Safaricom
     */
    public function c2bsimulate($phonenumber, $amount, $shortcode, $command_id, $account_number = NULL)
    {

        if ($command_id == 'CustomerPayBillOnline') {
            //Paybill Request Body
            $data = [
                'Msisdn' => $this->phoneValidator($phonenumber),
                'Amount' => (int) $amount,
                'BillRefNumber' => $account_number, //Account number for a paybill
                'CommandID' => $command_id, //Can be CustomerPayBillOnline for a paybill
                'ShortCode' => $shortcode // Paybill
            ];
        } else {
            //Till Number Request Body
            $data = [
                'Msisdn' => $this->phoneValidator($phonenumber),
                'Amount' => (int) $amount,
                'CommandID' => $command_id, ///Can be CustomerBuyGoodsOnline for a till number
                'ShortCode' => $shortcode //  Till Number
            ];
        }

        $url = $this->url . '/mpesa/c2b/v2/simulate';


        $response = $this->MpesaRequest($url, $data);
        return $response;
    }


    /**
     * Transaction Status
     *
     * This method is used to check the status of a transaction.
     *
     * @param int $shortcode Organization/MSISDN receiving the transaction
     * @param string $transactionid Unique identifier to identify a transaction on M-Pesa
     * @param int $identiertype identifier to identify the orginization
     * @param string $remarks Any additional information. Must be present.
     * @return object Curl Response from Safaricom
     */
    public function transactionStatus($shortcode, $transactionid, $identiertype, $remarks)
    {
        $url = $this->url . "/mpesa/transactionstatus/v1/query";

        $body = [

            "Initiator" => $this->initiator_name,
            "SecurityCredential" => $this->security_credential,
            "CommandID" => "TransactionStatusQuery",
            "TransactionID" => $transactionid,
            "PartyA" => $shortcode,
            "IdentifierType" => $identiertype, //1 – MSISDN 2 – Till Number 4 – Organization short code
            "ResultURL" => $this->statusresult,
            "QueueTimeOutURL" => $this->statustimeout,
            "Remarks" => $remarks,
            "Occassion" => "",
        ];

        $response = $this->MpesaRequest($url, $body);
        return $response;
    }

    /**
     * Account Balance
     *
     * This method is used to enquire the balance on an M-Pesa BuyGoods (Till Number)
     *
     * @param int $shortcode Organization/MSISDN receiving the transaction
     * @param int $identiertype identifier to identify the orginization
     * @param string $remarks Any additional information. Must be present.
     * @return object Curl Response from Safaricom
     */
    public function accountBalance($shortcode, $identiertype, $remarks)
    {
        $url = $this->url . "/mpesa/accountbalance/v1/query";

        $body = [
            "Initiator" => $this->initiator_name,
            "SecurityCredential" => $this->security_credential,
            "CommandID" => "AccountBalance",
            "PartyA" => $shortcode,
            "IdentifierType" => $identiertype, //1 – MSISDN 2 – Till Number 4 – Organization short code
            "Remarks" => $remarks,
            "ResultURL" => $this->balresult,
            "QueueTimeOutURL" => $this->baltimeout,
        ];

        $response = $this->MpesaRequest($url, $body);
        return $response;
    }

    /**
     * Reversal
     *
     * This method is used to reverse an M-Pesa transaction
     *
     * @param double $amount The amount transacted in that transaction to be reversed, down to the cent.
     * @param int $shortcode Your Org's shortcode.
     * @param string $transactionid This is the M-Pesa Transaction ID of the transaction which you wish to reverse.
     * @param string $remarks Any additional information. Must be present.
     * @return object Curl Response from Safaricom
     */
    public function reversal($shortcode, $transactionid, $amount, $remarks)
    {
        $url = $this->url  . "/mpesa/reversal/v1/request";

        $body = [

            "Initiator" => $this->initiator_name,
            "SecurityCredential" => $this->security_credential,
            "CommandID" => "TransactionReversal",
            "TransactionID" => $transactionid,
            "Amount" => $amount,
            "ReceiverParty" => $shortcode,
            "RecieverIdentifierType" => "11",
            "ResultURL" => $this->reverseresult,
            "QueueTimeOutURL" => $this->reversetimeout,
            "Remarks" => $remarks,
            "Occasion" => ""
        ];

        $response = $this->MpesaRequest($url, $body);
        return $response;
    }
}
