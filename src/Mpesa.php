<?php

namespace Iankumu\Mpesa;

use Iankumu\Mpesa\Exceptions\CallbackException;
use Iankumu\Mpesa\Utils\MpesaHelper;

class Mpesa
{
    use MpesaHelper;

    /**
     * The encrypted API credentials
     *
     * @var string
     */
    public $security_credential;

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
     * The Base URL
     *
     * @var string
     */
    public $url;


    public function __construct()
    {
        $this->url = config('mpesa.environment') == 'sandbox'
            ? 'https://sandbox.safaricom.co.ke'
            : 'https://api.safaricom.co.ke';

        $this->security_credential = $this->generate_security_credential();
        $this->shortcode = $this->getConfig('shortcode');
        $this->initiator_name = $this->getConfig('initiator_name');
        $this->b2c_shortcode = $this->getConfig('b2c_shortcode');
    }

    /**
     * Mpesa STKPUSH
     *
     * This method is used to initiate an online payment on behalf of a customer
     *
     * @param int $phonenumber The phone number that will receive the stkpush prompt in the format 254xxxxxxxxx
     * @param int $amount The amount to be transacted
     * @param string $account_number The account number for a paybill
     * @param string|null $callbackurl The callback url for Mpesa Express
     * @return \Illuminate\Http\Client\Response
     */
    public function stkpush($phonenumber, $amount, $account_number, $callbackurl = null)
    {
        $url = $this->url . '/mpesa/stkpush/v1/processrequest';
        $data = [
            'BusinessShortCode' => $this->shortcode, //Has to be a paybill and not a till number since it is not supported
            'Password' => $this->LipaNaMpesaPassword(),
            'Timestamp' => $this->getFormattedTimeStamp(),
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int) $amount,
            'PartyA' => $this->phoneValidator($phonenumber), // replace this with your phone number
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $this->phoneValidator($phonenumber), // replace this with your phone number
            'AccountReference' => $account_number, //Account Number for a paybill..Maximum of 12 Characters.
            'TransactionDesc' => 'Payment', //Maximum of 13 Characters.
            'CallBackURL' => $this->resolveCallbackUrl($callbackurl, 'callback_url', 'callback_url'),
        ];

        return $this->MpesaRequest($url, $data);
    }

    /**
     * Mpesa STKPUSH Query
     *
     * This method is used to check the status of a Lipa Na M-Pesa Online Payment.
     *
     * @param string $checkoutRequestId This is a global unique identifier of the processed checkout transaction request.
     * @return \Illuminate\Http\Client\Response
     */
    public function stkquery($checkoutRequestId)
    {
        $post_data = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $this->LipaNaMpesaPassword(),
            'Timestamp' => $this->getFormattedTimeStamp(),
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
     * @param int $phonenumber The phone number of the recipient in the format 254xxxxxxxxx
     * @param string $command_id The type of transaction being made. Can be SalaryPayment,BusinessPayment or PromotionPayment
     * @param int $amount The amount to send to the recipient
     * @param string $remarks Any additional information. Must be present.
     * @param string|null $result_url The Result Url where payload will be sent.
     * @param string|null $timeout_url The Timeout Url where payload will be sent. Must be present.
     * @return \Illuminate\Http\Client\Response
     */
    public function b2c($phonenumber, $command_id, $amount, $remarks, $result_url = null, $timeout_url = null)
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
            'Occassion' => '', //can be null
            'ResultURL' => $this->resolveCallbackUrl($result_url, 'b2c_result_url', 'b2c_result_url'),
            'QueueTimeOutURL' => $this->resolveCallbackUrl($timeout_url, 'b2c_timeout_url', 'b2c_timeout_url'),
        ];

        return $this->MpesaRequest($url, $body);
    }

    /**
     * Business to Client With Validation
     *
     * This method is used to send money to a client's Mpesa account.
     * It requires one to provide the id number of the recipient, and fails if the id number does not match the phone number.
     *
     * @param int $phonenumber The phone number of the recipient in the format 254xxxxxxxxx
     * @param string $command_id The type of transaction being made. Can be SalaryPayment,BusinessPayment or PromotionPayment
     * @param int $amount The amount to send to the recipient
     * @param string $remarks Any additional information. Must be present.
     * @param string $id_number The id number of the recipient
     * @param string|null $result_url The Result Url where payload will be sent.
     * @param string|null $timeout_url The Timeout Url where payload will be sent. Must be present.
     * @return \Illuminate\Http\Client\Response
     */
    public function validated_b2c($phonenumber, $command_id, $amount, $remarks, $id_number, $result_url = null, $timeout_url = null)
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
            'Occassion' => '', //can be null
            'OriginatorConversationID' => $this->getFormattedTimeStamp(),
            'IDType' => '01', //01 for national id
            'IDNumber' => $id_number,
            'ResultURL' => $this->resolveCallbackUrl($result_url, 'b2c_result_url', 'b2c_result_url'),
            'QueueTimeOutURL' => $this->resolveCallbackUrl($timeout_url, 'b2c_timeout_url', 'b2c_timeout_url'),
        ];

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
     * * @return \Illuminate\Http\Client\Response
     */
    public function b2b($receiver_shortcode, $command_id, $amount, $remarks, $account_number = null, $b2b_result_url = null, $b2b_timeout_url = null)
    {
        $url = $this->url . "/mpesa/b2b/v1/paymentrequest";

        $body = [
            "Initiator" => $this->initiator_name,
            "SecurityCredential" => $this->security_credential,
            "CommandID" => $command_id, //can be BusinessPayBill, MerchantToMerchantTransfer, MerchantTransferFromMerchantToWorking, MerchantServicesMMFAccountTransfer, AgencyFloatAdvance
            "SenderIdentifierType" => '4', //4 for shortcode
            "RecieverIdentifierType" => '4', //4 for shortcode
            "Amount" => $amount,
            "PartyA" => $this->b2c_shortcode,
            "PartyB" => $receiver_shortcode,
            "AccountReference" => $account_number,
            "Remarks" => $remarks,
            'ResultURL' => $this->resolveCallbackUrl($b2b_result_url, 'b2b_result_url', 'b2b_result_url'),
            'QueueTimeOutURL' => $this->resolveCallbackUrl($b2b_timeout_url, 'b2b_timeout_url', 'b2b_timeout_url'),
        ];
        if ($command_id == 'BusinessPayBill') {
            if ($account_number == null) {
                throw new \Exception("Account Number is required for BusinessPayBill CommandID");
            }
            $body['AccountReference'] = $account_number;
        }

        return $this->MpesaRequest($url, $body);
    }

    /**
     * Client to Business
     *
     * This method is used to register URLs for callbacks when money is sent from the MPesa toolkit menu
     *
     * @param string $shortcode The till number or paybill number the urls will be associated with
     * @param string|null $confirmurl The URL that receives the confirmation of the transaction
     * @param string|null $validateurl The URL that receives the validation of the transaction
     * @return \Illuminate\Http\Client\Response
     */
    public function c2bregisterURLS($shortcode, $confirmurl = null, $validateurl = null)
    {
        $url = $this->url . '/mpesa/c2b/v2/registerurl';

        $body = [
            'ShortCode' => $shortcode,
            'ResponseType' => 'Completed', //Completed or Cancelled
            'ConfirmationURL' => $this->resolveCallbackUrl($confirmurl, 'c2b_confirmation_url', 'c2b_confirmation_url'),
            'ValidationURL' => $this->resolveCallbackUrl($validateurl, 'c2b_validation_url', 'c2b_validation_url'),
        ];

        return $this->MpesaRequest($url, $body);
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
     * @return \Illuminate\Http\Client\Response
     */
    public function c2bsimulate($phonenumber, $amount, $shortcode, $command_id, $account_number = null)
    {
        if ($command_id == 'CustomerPayBillOnline') {
            //Paybill Request Body
            $data = [
                'Msisdn' => $this->phoneValidator($phonenumber),
                'Amount' => (int) $amount,
                'BillRefNumber' => $account_number, //Account Number for a paybill..Maximum of 12 Characters.
                'CommandID' => $command_id, //Can be CustomerPayBillOnline for a paybill
                'ShortCode' => $shortcode, // Paybill
            ];
        } else {
            //Till Number Request Body
            $data = [
                'Msisdn' => $this->phoneValidator($phonenumber),
                'Amount' => (int) $amount,
                'CommandID' => $command_id, //Can be CustomerBuyGoodsOnline for a till number
                'ShortCode' => $shortcode, // Till Number
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
     * @param int $shortcode Organization/MSISDN receiving the transaction
     * @param string $transactionid Unique identifier to identify a transaction on M-Pesa
     * @param int $identiertype identifier to identify the orginization
     * @param string $remarks Any additional information. Must be present.
     * @param string|null $result_url The Result Url where payload will be sent.
     * @param string|null $timeout_url The Timeout Url where payload will be sent.
     * @return \Illuminate\Http\Client\Response
     */
    public function transactionStatus($shortcode, $transactionid, $identiertype, $remarks, $result_url = null, $timeout_url = null)
    {
        $url = $this->url . '/mpesa/transactionstatus/v1/query';

        $body = [
            'Initiator' => $this->initiator_name,
            'SecurityCredential' => $this->security_credential,
            'CommandID' => 'TransactionStatusQuery',
            'TransactionID' => $transactionid,
            'PartyA' => $shortcode,
            'IdentifierType' => $identiertype, //1 – MSISDN 2 – Till Number 4 – Organization short code
            'Remarks' => $remarks,
            'Occassion' => '',
            'ResultURL' => $this->resolveCallbackUrl($result_url, 'status_result_url', 'status_result_url'),
            'QueueTimeOutURL' => $this->resolveCallbackUrl($timeout_url, 'status_timeout_url', 'status_timeout_url'),
        ];

        return $this->MpesaRequest($url, $body);
    }

    /**
     * Account Balance
     *
     * This method is used to enquire the balance on an M-Pesa BuyGoods (Till Number)
     *
     * @param int $shortcode Organization/MSISDN receiving the transaction
     * @param int $identiertype identifier to identify the orginization
     * @param string $remarks Any additional information. Must be present.
     * @param string|null $result_url The Result Url where payload will be sent.
     * @param string|null $timeout_url The Timeout Url where payload will be sent.
     * @return \Illuminate\Http\Client\Response
     */
    public function accountBalance($shortcode, $identiertype, $remarks, $result_url = null, $timeout_url = null)
    {
        $url = $this->url . '/mpesa/accountbalance/v1/query';

        $body = [
            'Initiator' => $this->initiator_name,
            'SecurityCredential' => $this->security_credential,
            'CommandID' => 'AccountBalance',
            'PartyA' => $shortcode,
            'IdentifierType' => $identiertype, //1 – MSISDN 2 – Till Number 4 – Organization short code
            'Remarks' => $remarks,
            'ResultURL' => $this->resolveCallbackUrl($result_url, 'balance_result_url', 'balance_result_url'),
            'QueueTimeOutURL' => $this->resolveCallbackUrl($timeout_url, 'balance_timeout_url', 'balance_timeout_url'),
        ];

        return $this->MpesaRequest($url, $body);
    }

    /**
     * Reversal
     *
     * This method is used to reverse an M-Pesa transaction
     *
     * @param float $amount The amount transacted in that transaction to be reversed, down to the cent.
     * @param int $shortcode Your Org's shortcode.
     * @param string $transactionid This is the M-Pesa Transaction ID of the transaction which you wish to reverse.
     * @param string $remarks Any additional information. Must be present.
     * @return \Illuminate\Http\Client\Response
     */
    public function reversal($shortcode, $transactionid, $amount, $remarks, $reverseresulturl = null, $reversetimeouturl = null)
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
            'Remarks' => $remarks,
            'Occasion' => '',
            'ResultURL' => $this->resolveCallbackUrl($reverseresulturl, 'reversal_result_url', 'reversal_result_url'),
            'QueueTimeOutURL' => $this->resolveCallbackUrl($reversetimeouturl, 'reversal_timeout_url', 'reversal_timeout_url'),
        ];

        return $this->MpesaRequest($url, $body);
    }
}
