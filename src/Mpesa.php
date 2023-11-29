<?php

namespace Iankumu\Mpesa;

use Iankumu\Mpesa\Exceptions\CallbackException;
use Iankumu\Mpesa\Utils\MpesaHelper;
use Illuminate\Support\Carbon;

class Mpesa
{
    use MpesaHelper;

    /**
     * The encrypted API credentials
     *
     * @var string
     */
    private $security_credential;

    /**
     * The Lipa Na MPesa shortcode
     *
     * @var int
     */
    public $shortcode;

    /**
     * The Mpesa B2C shortcode
     *
     * @var int
     */
    public $b2c_shortcode;

    /**
     * The Mpesa portal Username
     *
     * @var string
     */
    public $initiator_name;

    /**
     * The Mpesa Environment
     *
     * @var string
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

    public $b2b_result_url;

    public $b2b_timeout_url;

    /**
     * The Base URL
     *
     * @var string
     */
    public $url;

    /**
     * Construct method
     *
     * Initializes the class with API values.
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
        $this->b2b_result_url = config('mpesa.b2b_result_url');
        $this->b2b_timeout_url = config('mpesa.b2b_timeout_url');
        $this->url = config('mpesa.environment') == 'sandbox'
            ? 'https://sandbox.safaricom.co.ke'
            : 'https://api.safaricom.co.ke';
    }

    /**
     * Mpesa STKPUSH
     *
     * This method is used to initiate an online payment on behalf of a customer
     *
     * @param  int  $phonenumber The phone number that will receive the stkpush prompt in the format 254xxxxxxxxx
     * @param  int  $amount The amount to be transacted
     * @param  string  $account_number The account number for a paybill
     * @return \Illuminate\Http\Client\Response
     */
    public function stkpush($phonenumber, $amount, $account_number, $callbackurl = null)
    {
        $url = $this->url . '/mpesa/stkpush/v1/processrequest';
        $data = [
            //Fill in the request parameters with valid values
            'BusinessShortCode' => $this->shortcode, //Has to be a paybill and not a till number since it is not supported
            'Password' => $this->lipaNaMpesaPassword(),
            'Timestamp' => Carbon::rawParse('now')->format('YmdHis'),
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int) $amount,
            'PartyA' => $this->phoneValidator($phonenumber), // replace this with your phone number
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $this->phoneValidator($phonenumber), // replace this with your phone number
            'AccountReference' => $account_number, //Account Number for a paybill..Maximum of 12 Characters.
            'TransactionDesc' => 'Payment', //Maximum of 13 Characters.
        ];

        //url should be https and should not contain keywords such as mpesa,safaricom etc
        if (!is_null($callbackurl) && is_null($this->stkcallback)) {
            $data += [
                'CallBackURL' => $callbackurl,
            ];
        } elseif (is_null($callbackurl) && !is_null($this->stkcallback)) {
            $data += [
                'CallBackURL' => $this->stkcallback,
            ];
        } elseif (!is_null($callbackurl) && !is_null($this->stkcallback)) {
            $data += [
                'CallBackURL' => $callbackurl,
            ];
        } else {
            throw CallbackException::make(
                'callback_url',
                'Ensure you have set a Callback URL in the mpesa config file'
            );
        }

        return $this->MpesaRequest($url, $data);
    }

    /**
     * Mpesa STKPUSH Query
     *
     * This method is used to check the status of a Lipa Na M-Pesa Online Payment.
     *
     * @param  string  $checkoutRequestId This is a global unique identifier of the processed checkout transaction request.
     * @return \Illuminate\Http\Client\Response
     */
    public function stkquery($checkoutRequestId)
    {
        $post_data = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $this->lipaNaMpesaPassword(),
            'Timestamp' => Carbon::rawParse('now')->format('YmdHis'),
            'CheckoutRequestID' => $checkoutRequestId,
        ];

        $url = $this->url . '/mpesa/stkpushquery/v1/query';

        return $this->MpesaRequest($url, $post_data);
    }

    /**
     * Business to Client
     *
     * This method is used to send money to a client's Mpesa account.
     *
     * @param  int  $phonenumber The phone number of the recipient in the format 254xxxxxxxxx
     * @param  string  $command_id The type of transaction being made. Can be SalaryPayment,BusinessPayment or PromotionPayment
     * @param  int  $amount The amount to send to the recipient
     * @param  string  $remarks Any additional information. Must be present.
     * @return \Illuminate\Http\Client\Response
     */
    public function b2c($phonenumber, $command_id, $amount, $remarks)
    {
        $url = $this->url . '/mpesa/b2c/v1/paymentrequest';

        $body = [
            'InitiatorName' => $this->initiator_name,
            'SecurityCredential' => $this->security_credential,
            'CommandID' => $command_id, //can be SalaryPayment,BusinessPayment or PromotionPayment
            'Amount' => $amount,
            'PartyA' => $this->b2c_shortcode,
            'PartyB' => $this->phoneValidator($phonenumber),
            'Remarks' => $remarks,
            'QueueTimeOutURL' => $this->b2ctimeout,
            'ResultURL' => $this->b2cresult,
            'Occassion' => '', //can be null
        ];

        if (is_null($this->b2cresult)) {
            throw CallbackException::make(
                'b2c_result_url',
                'Ensure you have set the B2C Result URL in the mpesa config file'
            );
        }

        if (is_null($this->b2ctimeout)) {
            throw CallbackException::make(
                'b2c_timeout_url',
                'Ensure you have set the B2C Timeout URL in the mpesa config file'
            );
        }

        return $this->MpesaRequest($url, $body);
    }

    /**
     * Business to Client With Validation
     *
     * This method is used to send money to a client's Mpesa account.
     * It requires one to provide the id number of the recipient, and fails if the id number does not match the phone number.
     * COMMON ERRORS
     * 1. Duplicate Originator Conversation ID. -> This means you have already sent a request with the same OriginatorConversationID
     * 2. Invalid Access    Token - Invalid API call as no apiproduct match found”. -> The Daraja sandbox/ production app
     * you are using to run the tests does not
     * have the B2C with validation product.
     * Send an email to
     * apisupport@safaricom.co.ke requesting
     * for addition of the product to your
     * sandbox app. Specify your
     * prod/sandbox app to which the product
     * should be added.
     *
     * @param  int  $phonenumber The phone number of the recipient in the format 254xxxxxxxxx
     * @param  string  $command_id The type of transaction being made. Can be SalaryPayment,BusinessPayment or PromotionPayment
     * @param  int  $amount The amount to send to the recipient
     * @param  string  $remarks Any additional information. Must be present.
     * @param  string  $id_number The id number of the recipient
     * @return \Illuminate\Http\Client\Response
     */
    public function validated_b2c($phonenumber, $command_id, $amount, $remarks, $id_number)
    {
        $url = $this->url . '/mpesa/b2cvalidate/v2/paymentrequest';
        $body = [
            'InitiatorName' => $this->initiator_name,
            'SecurityCredential' => $this->security_credential,
            'CommandID' => $command_id, //can be SalaryPayment,BusinessPayment or PromotionPayment
            'Amount' => $amount,
            'PartyA' => $this->b2c_shortcode,
            'PartyB' => $this->phoneValidator($phonenumber),
            'Remarks' => $remarks,
            'QueueTimeOutURL' => $this->b2ctimeout,
            'ResultURL' => $this->b2cresult,
            'Occassion' => '', //can be null
            'OriginatorConversationID' => Carbon::rawParse('now')->format('YmdHis'), //unique id for the transaction
            'IDType' => '01', //01 for national id
            'IDNumber' => $id_number,
        ];

        if (is_null($this->b2cresult)) {
            throw CallbackException::make(
                'b2c_result_url',
                'Ensure you have set the B2C Result URL in the mpesa config file'
            );
        }

        if (is_null($this->b2ctimeout)) {
            throw CallbackException::make(
                'b2c_timeout_url',
                'Ensure you have set the B2C Timeout URL in the mpesa config file'
            );
        }

        return $this->MpesaRequest($url, $body);
    }

    /**
     * Business to Business
     *
     * This method is used to send money to a business's Mpesa account.
     *
     * @param int $amount The amount to send to the recipient
     * @param int $receiver_shortcode The shortcode of the recipient
     * @param string $command_id The type of transaction being made. Can be BusinessPayBill, MerchantToMerchantTransfer, MerchantTransferFromMerchantToWorking, MerchantServicesMMFAccountTransfer, AgencyFloatAdvance
     * @param string $remarks Any additional information. Must be present.
     * @param string $account_number Required for “BusinessPaybill” CommandID.
     * @return \Illuminate\Http\Client\Response
     */
    public function b2b($receiver_shortcode, $command_id, $amount, $remarks, $account_number = null)
    {
        $url = $this->url . "/mpesa/b2b/v1/paymentrequest";

        $body = [
            "Initiator" => $this->initiator_name,
            "SecurityCredential" => $this->security_credential,
            "CommandID" => $command_id, //can be BusinessPayBill, MerchantToMerchantTransfer, MerchantTransferFromMerchantToWorking, MerchantServicesMMFAccountTransfer, AgencyFloatAdvance
            "SenderIdentifierType" => '4', //4 for shortcode
            "RecieverIdentifierType" => '4', //4 for shortcode
            "Amount" => $amount,
            "PartyA" => $this->b2c_shortcode, //uses same shortcode as b2c
            "PartyB" => $receiver_shortcode,
            "AccountReference" => $account_number,
            "Remarks" => $remarks
        ];
        if ($command_id == 'BusinessPayBill') {
            if ($account_number == null)
                throw new \Exception("Account Number is required for BusinessPayBill CommandID");

            $body['AccountReference'] = $account_number;
        }
        //check urls
        if (!filter_var($this->b2b_result_url, FILTER_VALIDATE_URL)) {
            throw new CallbackException("Result URL is not valid");
        }
        if (!filter_var($this->b2b_timeout_url, FILTER_VALIDATE_URL)) {
            throw new CallbackException("Timeout URL is not valid");
        }

        $body['QueueTimeOutURL'] = $this->b2b_timeout_url;
        $body['ResultURL'] = $this->b2b_result_url;

        return $this->MpesaRequest($url, $body);
    }

    /**
     * Client to Business
     *
     * This method is used to register URLs for callbacks when money is sent from the MPesa toolkit menu
     *
     * @param  string  $shortcode The till number or paybill number the urls will be associated with
     * @return \Illuminate\Http\Client\Response
     */
    public function c2bregisterURLS($shortcode)
    {

        $url = $this->url . '/mpesa/c2b/v2/registerurl';

        $body = [
            'ShortCode' => $shortcode,
            'ResponseType' => 'Completed', //Completed or Cancelled
            'ConfirmationURL' => $this->c2bconfirm, //url should be https and should not contain keywords such as mpesa,safaricom etc
            'ValidationURL' => $this->c2bvalidate, //url should be https and should not contain keywords such as mpesa,safaricom etc
        ];

        if (is_null($this->c2bconfirm)) {
            throw CallbackException::make(
                'c2b_confirmation_url',
                'Ensure you have set the C2B Confirmation URL in the mpesa config file'
            );
        }

        if (is_null($this->c2bvalidate)) {
            throw CallbackException::make(
                'c2b_validation_url',
                'Ensure you have set the C2B Validate URL in the mpesa config file'
            );
        }

        return $this->MpesaRequest($url, $body);
    }

    /**
     * C2B Simulation
     *
     * This method is used to simulate a C2B Transaction to test your ConfirmURL and ValidationURL in the Client to Business method
     *
     * @param  int  $amount The amount to send to shortcode
     * @param  int  $phonenumber A dummy Safaricom phone number to simulate transaction in the format 254xxxxxxxxx
     * @param  string  $shortcode The Paybill/Till number receiving the funds
     * @param  string  $command_id The Type of transaction. Whether it is a paybill transaction(CustomerPayBillOnline) or a Till number transaction(CustomerBuyGoodsOnline)
     * @param  string  $account_number The account number for a paybill. The default is null
     * @return \Illuminate\Http\Client\Response
     */
    public function c2bsimulate($phonenumber, $amount, $shortcode, $command_id, $account_number = null)
    {

        if ($command_id == 'CustomerPayBillOnline') {
            //Paybill Request Body
            $data = [
                'Msisdn' => $this->phoneValidator($phonenumber),
                'Amount' => (int) $amount,
                'BillRefNumber' => $account_number, //Account number for a paybill
                'CommandID' => $command_id, //Can be CustomerPayBillOnline for a paybill
                'ShortCode' => $shortcode, // Paybill
            ];
        } else {
            //Till Number Request Body
            $data = [
                'Msisdn' => $this->phoneValidator($phonenumber),
                'Amount' => (int) $amount,
                'CommandID' => $command_id, ///Can be CustomerBuyGoodsOnline for a till number
                'ShortCode' => $shortcode, //  Till Number
            ];
        }

        $url = $this->url . '/mpesa/c2b/v2/simulate';

        return $this->MpesaRequest($url, $data);
    }

    /**
     * Transaction Status
     *
     * This method is used to check the status of a transaction.
     *
     * @param  int  $shortcode Organization/MSISDN receiving the transaction
     * @param  string  $transactionid Unique identifier to identify a transaction on M-Pesa
     * @param  int  $identiertype identifier to identify the orginization
     * @param  string  $remarks Any additional information. Must be present.
     * @return \Illuminate\Http\Client\Response
     */
    public function transactionStatus($shortcode, $transactionid, $identiertype, $remarks)
    {
        $url = $this->url . '/mpesa/transactionstatus/v1/query';

        $body = [

            'Initiator' => $this->initiator_name,
            'SecurityCredential' => $this->security_credential,
            'CommandID' => 'TransactionStatusQuery',
            'TransactionID' => $transactionid,
            'PartyA' => $shortcode,
            'IdentifierType' => $identiertype, //1 – MSISDN 2 – Till Number 4 – Organization short code
            'ResultURL' => $this->statusresult,
            'QueueTimeOutURL' => $this->statustimeout,
            'Remarks' => $remarks,
            'Occassion' => '',
        ];

        if (is_null($this->statusresult)) {
            throw CallbackException::make(
                'status_result_url',
                'Ensure you have set the Transaction Status Result URL in the mpesa config file'
            );
        }

        if (is_null($this->statustimeout)) {
            throw CallbackException::make(
                'status_timeout_url',
                'Ensure you have set the Transaction Status Timeout URL in the mpesa config file'
            );
        }

        return $this->MpesaRequest($url, $body);
    }

    /**
     * Account Balance
     *
     * This method is used to enquire the balance on an M-Pesa BuyGoods (Till Number)
     *
     * @param  int  $shortcode Organization/MSISDN receiving the transaction
     * @param  int  $identiertype identifier to identify the orginization
     * @param  string  $remarks Any additional information. Must be present.
     * @return \Illuminate\Http\Client\Response
     */
    public function accountBalance($shortcode, $identiertype, $remarks)
    {
        $url = $this->url . '/mpesa/accountbalance/v1/query';

        $body = [
            'Initiator' => $this->initiator_name,
            'SecurityCredential' => $this->security_credential,
            'CommandID' => 'AccountBalance',
            'PartyA' => $shortcode,
            'IdentifierType' => $identiertype, //1 – MSISDN 2 – Till Number 4 – Organization short code
            'Remarks' => $remarks,
            'ResultURL' => $this->balresult,
            'QueueTimeOutURL' => $this->baltimeout,
        ];

        if (is_null($this->statusresult)) {
            throw CallbackException::make(
                'balance_result_url',
                'Ensure you have set the Account Balance Result URL in the mpesa config file'
            );
        }

        if (is_null($this->statustimeout)) {
            throw CallbackException::make(
                'balance_timeout_url',
                'Ensure you have set the Account Balance Timeout URL in the mpesa config file'
            );
        }

        return $this->MpesaRequest($url, $body);
    }

    /**
     * Reversal
     *
     * This method is used to reverse an M-Pesa transaction
     *
     * @param  float  $amount The amount transacted in that transaction to be reversed, down to the cent.
     * @param  int  $shortcode Your Org's shortcode.
     * @param  string  $transactionid This is the M-Pesa Transaction ID of the transaction which you wish to reverse.
     * @param  string  $remarks Any additional information. Must be present.
     * @return \Illuminate\Http\Client\Response
     */
    public function reversal($shortcode, $transactionid, $amount, $remarks)
    {
        $url = $this->url . '/mpesa/reversal/v1/request';

        $body = [

            'Initiator' => $this->initiator_name,
            'SecurityCredential' => $this->security_credential,
            'CommandID' => 'TransactionReversal',
            'TransactionID' => $transactionid,
            'Amount' => $amount,
            'ReceiverParty' => $shortcode,
            'RecieverIdentifierType' => '11',
            'ResultURL' => $this->reverseresult,
            'QueueTimeOutURL' => $this->reversetimeout,
            'Remarks' => $remarks,
            'Occasion' => '',
        ];

        if (is_null($this->reverseresult)) {
            throw CallbackException::make(
                'reversal_result_url',
                'Ensure you have set the Reversal Result URL in the mpesa config file'
            );
        }

        if (is_null($this->reversetimeout)) {
            throw CallbackException::make(
                'reversal_timeout_url',
                'Ensure you have set the Reversal Timeout URL in the mpesa config file'
            );
        }

        return $this->MpesaRequest($url, $body);
    }
}
