# Usage Guide

This guide provides practical examples of how to use the Mpesa package. You can find the full documentation of the Mpesa APIs on the [official Mpesa documentation](https://developer.safaricom.co.ke/docs).

## Configuration

Before you start interacting with Mpesa APIs, you need to set the `mpesa_consumer_key` and the `mpesa_consumer_secret`in the`config/mpesa.php` file. You can get this when you create an app in your [developer account](https://developer.safaricom.co.ke/MyApps).

You can also change the `environment` in your `config/mpesa.php` file to suit your needs. You can set it to either `sandbox` or `production` with the default being `sandbox`.

**Testing on Localhost:**
When testing with the sandbox environment on localhost, use tools like [Localhost.run](https://localhost.run/) or [LocalTunnel](https://localtunnel.github.io/www/) to expose your callback URLs to the internet. Note that Safaricom may block [Ngrok](https://ngrok.com/).

You can run this command in your terminal to expose port 8000 to the internet.

```bash
ssh -R 80:localhost:8000 nokey@localhost.run
```

# Important Tips

> `Don't` include these keywords in your urls when registering your callbacks
>
> - mpesa
> - safaricom
> - any other keyword related to Safaricom

> Ensure all your callback urls are `https`.

## STKPUSH (Lipa Na M-Pesa Online)

This API allows you to initiate **PayBill** (`CustomerPayBillOnline`) or **Till Number** (`CustomerBuyGoodsOnline`) transactions via STK Push. It requires your shortcode (PayBill or Till), passkey, and the appropriate transaction type.

> **Note**
>
> - **PayBill Flow** (`CustomerPayBillOnline`) requires an `account_number` (AccountReference).
> - **Till Number Flow** (`CustomerBuyGoodsOnline`) You can pass any value for account number (AccountReference).

This method requires a few parameters:

1. `phonenumber` - Phone number of the customer
2. `amount` - Amount of money to be paid by a customer
3. `account_number` - The account reference to identify a transaction
4. `callbackurl`(optional) - A URL where safaricom can send the response to.
5. `transaction_type` - can either be `Mpesa::PAYBILL` for paybill transactions or `Mpesa::TILL` for till number transactions.

### Paybill Example

Call the `stkpush()` method on the Mpesa facade. Make sure you have set `passkey` and `shortcode` in `config/mpesa.php`. If you have already registered a callback URL in the config, you can pass `null` for` $callbackurl`.

```php
use Iankumu\Mpesa\Facades\Mpesa;
$response=Mpesa::stkpush($phonenumber, $amount, $account_number,$callbackurl = null, Mpesa::PAYBILL);

$result = json_decode((string)$response);
return $result;
```

If you `have not` registered a `callback_url` in the `config/mpesa.php` file, you can pass the callback url as the fourth parameter to the method. Otherwise, you can leave it as null.

```php
use Iankumu\Mpesa\Facades\Mpesa;
$response=Mpesa::stkpush('0707070707', 100, '12345',null,Mpesa::PAYBILL);

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;
```

OR

```php
use Iankumu\Mpesa\Facades\Mpesa;
$response=Mpesa::stkpush('0707070707', 100, '12345','https://test.test/callback',Mpesa::PAYBILL);

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;
```

### Till Number Example

To initiate a `Till Number (Buy Goods)` STK Push, call `stkpush()` with `$transactionType = Mpesa::TILL` and ensure you set a till number in `config/mpesa.php`.
Note these points:

- Use a Till Number shortcode (not a PayBill shortcode).
- Ensure you set both the business short code and till number

```php

use Iankumu\Mpesa\Facades\Mpesa;

$response = Mpesa::stkpush(
    $phoneNumber     = '254712345678',
    $amount          = 50,
    $account_number   = 'CUST-1',
    $callbackUrl     = 'https://example.com/till-callback',
    $transactionType = Mpesa::TILL
    $shortCodeType = 'C2B'
);

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;

```

You should get the following response after a successful execution

```json
{
  "MerchantRequestID": "",
  "CheckoutRequestID": "",
  "ResponseCode": "0",
  "ResponseDescription": "Success. Request accepted for processing",
  "CustomerMessage": "Success. Request accepted for processing"
}
```

Also a prompt should have been sent to the phonenumber you specified. After the customer `completes` or `cancels` the payment, Safaricom will send a response to your `$callbackUrl`:

```json
{
  "Body": {
    "stkCallback": {
      "MerchantRequestID": "5913-662870-1",
      "CheckoutRequestID": "ws_CO_DMZ_224117480_19012019164445976",
      "ResultCode": 0,
      "ResultDesc": "The service request is processed successfully.",
      "CallbackMetadata": {
        "Item": [
          {
            "Name": "Amount",
            "Value": 1
          },
          {
            "Name": "MpesaReceiptNumber",
            "Value": "NAJ3ABAMIR"
          },
          {
            "Name": "Balance"
          },
          {
            "Name": "TransactionDate",
            "Value": 20190119164514
          },
          {
            "Name": "PhoneNumber",
            "Value": 254705112855
          }
        ]
      }
    }
  }
}
```

## STKPUSH Query

This API enables you to query the status of STKPUSH payment. This is useful of you want to know what exatly happened when the prompt was sent to the user's phonenumber. You can get information regarding whether a user `cancelled the transaction`, if they had `insufficient balance` etc.

```php
use Iankumu\Mpesa\Facades\Mpesa;
$response=Mpesa::stkquery($checkoutRequestId, $shortCodeType = 'C2B');

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;

```

It is good practice to store the `checkoutRequestID` you received from the STKPUSH response as it can be used to query the status of that transaction using this endpoint.

This will return a response resembling the one below

```json
{
  "ResponseCode": "0",
  "ResponseDescription": "The service request has been accepted successfully",
  "MerchantRequestID": "22205-34066-1",
  "CheckoutRequestID": "ws_CO_13012021093521236557",
  "ResultCode": "0",
  "ResultDesc": "The service request is processed successfully."
}
```

## Register C2B Urls

This API enables you to register the callback URLs through which you can receive payload for payments to your paybill/till number. It is useful especially if you need your application to perform a task after payment has been made to your paybill/till number.

```php
use Iankumu\Mpesa\Facades\Mpesa;
$response=Mpesa::c2bregisterURLS($shortcode,$confirmurl = null, $validateurl = null, $shortCodeType = 'C2B');

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;
```

To register Urls ensure `c2b_validation_url` and `c2b_confirmation_url` are filled in `config/mpesa.php` or pass them as parameters when calling this function.

You can now call the `c2bregisterURLS()` method on `Mpesa` facade and pass a shortcode(paybill or till number) as a parameterS.

```php
use Iankumu\Mpesa\Facades\Mpesa;
$response = Mpesa::c2bregisterURLS(600998);

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;
```

OR

```php
use Iankumu\Mpesa\Facades\Mpesa;
$response = Mpesa::c2bregisterURLS(600998,'https://test.test/confirm','https://test.test/validate', 'C2B');

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;
```

Upon successful urls registration you should get the following response

```json
{
  "OriginatorCoversationID": "",
  "ResponseCode": "0",
  "ResponseDescription": "success"
}
```

## C2B Simulate API

You can use this method to simulate payment from clients and safaricom API. Before simulating you need to have registered your urls using `Register C2B Urls API`.

To simulate you need to pass these parameters to `c2bsimulate` method.

1. `phonenumber`- You can find this from the API test credentials
2. `amount`- The amount to be charged
3. `shortcode` Test paybill or till number
4. `command_id` The two transactions that can be simulated are `CustomerPayBillOnline` and `CustomerBuyGoodsOnline`.
5. `account_number`(optional) This is the test account number if the command id is `CustomerPayBillOnline`. You can provide any value as account_number if you are simulating till payments.
6. `shortCodeType` - The type of shortcode. Can be `C2B`, `B2C` or `B2B`. Default is `C2B`

```php
use Iankumu\Mpesa\Facades\Mpesa;
$response=Mpesa::c2bsimulate($phonenumber, $amount, $shortcode, $command_id, $account_number = NULL, $shortCodeType = 'C2B');

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;
```

Upon successful simulation you will receive a response similar to;

```json
{
  "ConversationID": "AG_20190115_00007fc37fc3db6e9562",
  "OriginatorCoversationID": "10028-4198443-1",
  "ResponseDescription": "Accept the service request successfully."
}
```

## B2C API

This API is useful if you want to make Payouts. These payouts can include salaries, promotional payments, cashbacks etc.

Before using this method, ensure you have added the `initiator_name`,`initiator_password`,`b2c_shortcode`,`b2c_result_url` and `b2c_timeout_url` configurations to the `config/mpesa.php` file or pass them as parameters when calling this function.

To use this API you need to call `b2c()` method on the `Mpesa` facade. This function accept the following parameters

1. `phonenumber` - Phone number of customer
2. `command_id` -This specifies the type of transaction.There are three types of transactions available: `SalaryPayment`, `BusinessPayment` or `PromotionPayment`
3. `Amount` - Amount of money to be sent to the customer.
4. `Remarks` - small decription of the payment being made.
5. `shortCodeType` - The type of shortcode. Can be `C2B`, `B2C` or `B2B`. Default is `B2C`

```php
use Iankumu\Mpesa\Facades\Mpesa;
$response =Mpesa::b2c($phonenumber, $command_id, $amount, $remarks,$result_url = null, $timeout_url = null, $shortCodeType = 'B2C');

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;
```

```php
use Iankumu\Mpesa\Facades\Mpesa;
$response=Mpesa::b2c('0708374149','SalaryPayment',1000,'salary payment');

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;
```

OR

```php
use Iankumu\Mpesa\Facades\Mpesa;
$response=Mpesa::b2c('0708374149','SalaryPayment',1000,'salary payment','https://test.test/result','https://test.test/timeout', 'B2C');

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;
```

In some cases, You might want to verify the recipient of the payment, using their ID number. To do this, you need to call the `validated_b2c` method, and pass the recipients ID Number as the 5th parameter.
If the ID provided does not match the phone number on Safaricom Database, the transaction will fail.

```php
use Iankumu\Mpesa\Facades\Mpesa;
$response=Mpesa::validated_b2c('0708374149','SalaryPayment',1000,'salary payment','12345678','https://test.test/result','https://test.test/timeout','B2C');

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;
```

Upon success you should receive a response similar to the one below

```json
{
  "ConversationID": "AG_20190117_00004636fb3ac56655df",
  "OriginatorConversationID": "17503-13504109-1",
  "ResponseCode": "0",
  "ResponseDescription": "Accept the service request successfully."
}
```

After a successful transaction you will get a callback via the `b2c_result_url` you specified in `config/mpesa.php` file.

```json
{
  "Result": {
    "ResultType": 0,
    "ResultCode": 0,
    "ResultDesc": "The service request is processed successfully.",
    "OriginatorConversationID": "10030-6237802-1",
    "ConversationID": "AG_20190119_000053c075d4e13cbeae",
    "TransactionID": "NAJ41H7YJQ",
    "ResultParameters": {
      "ResultParameter": [
        {
          "Key": "TransactionReceipt",
          "Value": "NAJ41H7YJQ"
        },
        {
          "Key": "TransactionAmount",
          "Value": 100
        },
        {
          "Key": "B2CChargesPaidAccountAvailableFunds",
          "Value": -495
        },
        {
          "Key": "B2CRecipientIsRegisteredCustomer",
          "Value": "Y"
        },
        {
          "Key": "TransactionCompletedDateTime",
          "Value": "19.01.2019 17:01:27"
        },
        {
          "Key": "ReceiverPartyPublicName",
          "Value": "254708374149 - John Doe"
        },
        {
          "Key": "B2CWorkingAccountAvailableFunds",
          "Value": 600000
        },
        {
          "Key": "B2CUtilityAccountAvailableFunds",
          "Value": 235
        }
      ]
    },
    "ReferenceData": {
      "ReferenceItem": {
        "Key": "QueueTimeoutURL",
        "Value": "https://internalsandbox.safaricom.co.ke/mpesa/b2cresults/v1/submit"
      }
    }
  }
}
```

## Transaction Status

This API can be used to view the details of a transaction.

Before using this method, ensure you have added the `initiator_name`,`initiator_password`,`status_result_url` and `status_timeout_url` configurations to the `config/mpesa.php` file.

The callbacks can be passed as parameters when calling this function.

To use this API you need to call `transactionStatus()` method on the `Mpesa` facade. This function accept the following parameters

1. `shortcode` - The Till number, paybill or phonenumber that received the payment
2. `transactionid` - Unique identifier to identify a transaction on M-Pesa
3. `identiertype` - Type of organization receiving the transaction. Can be `1` – MSISDN(phonenumber) `2` – Till Number `4` – Organization short code(paybill)
4. `Remarks` - small decription
5. `shortCodeType` - The type of shortcode. Can be `C2B`, `B2C` or `B2B`. Default is `C2B`

```php
use Iankumu\Mpesa\Facades\Mpesa;
$response = Mpesa::transactionStatus($shortcode, $transactionid, $identiertype, $remarks, $result_url = null, $timeout_url = null, $shortCodeType = 'C2B');

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;
```

```php
use Iankumu\Mpesa\Facades\Mpesa;
$response = Mpesa::transactionStatus('600999', 'OEI2AK4Q16', 4, 'Check transaction status','https://test.test/result','https://test.test/timeout', 'C2B');

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;
```

Upon success you should receive a response similar to the one below

```json
{
  "OriginatorConversationID": "1236-7134259-1",
  "ConversationID": "AG_20210709_1234409f86436c583e3f",
  "ResponseCode": "0",
  "ResponseDescription": "Accept the service request successfully."
}
```

Safaricom will respond with the result through the `status_result_url` callback you specified in `config/mpesa.php` file.

```json
{
  "Result": {
    "ConversationID": "AG_20180223_0000493344ae97d86f75",
    "OriginatorConversationID": "3213-416199-2",
    "ReferenceData": {
      "ReferenceItem": {
        "Key": "Occasion"
      }
    },
    "ResultCode": 0,
    "ResultDesc": "The service request is processed successfully.",
    "ResultParameters": {
      "ResultParameter": [
        {
          "Key": "DebitPartyName",
          "Value": "600310 - Safaricom333"
        },
        {
          "Key": "CreditPartyName",
          "Value": "254708374149 - John Doe"
        },
        {
          "Key": "OriginatorConversationID",
          "Value": "3211-416020-3"
        },
        {
          "Key": "InitiatedTime",
          "Value": 20180223054112
        },
        {
          "Key": "DebitAccountType",
          "Value": "Utility Account"
        },
        {
          "Key": "DebitPartyCharges",
          "Value": "Fee For B2C Payment|KES|22.40"
        },
        {
          "Key": "TransactionReason"
        },
        {
          "Key": "ReasonType",
          "Value": "Business Payment to Customer via API"
        },
        {
          "Key": "TransactionStatus",
          "Value": "Completed"
        },
        {
          "Key": "FinalisedTime",
          "Value": 20180223054112
        },
        {
          "Key": "Amount",
          "Value": 300
        },
        {
          "Key": "ConversationID",
          "Value": "AG_20180223_000041b09c22e613d6c9"
        },
        {
          "Key": "ReceiptNo",
          "Value": "MBN31H462N"
        }
      ]
    },
    "ResultType": 0,
    "TransactionID": "MBN0000000"
  }
}
```

## Account Balance

This API is useful or querying the balance on a specific asset(till number, paybill or Phonenumber)

Before using this method, ensure you have added the `initiator_name`,`initiator_password`,`balance_result_url` and `balance_timeout_url` configurations to the `config/mpesa.php` file.

The callbacks can be passed as parameters when calling this function.

To use this API you need to call `accountBalance()` method on the `Mpesa` facade. This function accept the following parameters

1. `shortcode` - The Till number, paybill or phonenumber that received the payment
2. `identiertype` - Type of organization receiving the transaction. Can be `1` – MSISDN(phonenumber) `2` – Till Number `4` – Organization short code(paybill)
3. `Remarks` - small decription
4. `shortCodeType` - The type of shortcode. Can be `C2B`, `B2C` or `B2B`. Default is `C2B`

```php
use Iankumu\Mpesa\Facades\Mpesa;
$response = Mpesa::accountBalance($shortcode, $identiertype, $remarks,$result_url = null, $timeout_url = null, $shortCodeType = 'C2B');

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;
```

```php
use Iankumu\Mpesa\Facades\Mpesa;
$response = Mpesa::accountBalance('600983', 4, 'check account balance','https://test.test/result','https://test.test/timeout', 'C2B');

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;
```

## Reversals

This API is useful for Reversing Mpesa Transactions.

Before using this method, ensure you have added the `initiator_name`,`initiator_password`,`reversal_result_url` and `reversal_timeout_url` configurations to the `config/mpesa.php` file.

The callbacks can be passed as parameters when calling this function.

To use this API you need to call `reversal()` method on the `Mpesa` facade. This function accept the following parameters

1. `shortcode` - The Till number, paybill or phonenumber that received the payment
2. `transactionid` - Unique identifier to identify a transaction on M-Pesa
3. `amount` - The Amount to be reversed
4. `Remarks` - small decription
5. `shortCodeType` - The type of shortcode. Can be `C2B`, `B2C` or `B2B`. Default is `C2B`

```php
use Iankumu\Mpesa\Facades\Mpesa;
$response = Mpesa::reversal($shortcode, $transactionid, $amount, $remarks,$reverseresulturl = null, $reversetimeouturl = null, $shortCodeType = 'C2B');

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;
```

```php
use Iankumu\Mpesa\Facades\Mpesa;
$response = Mpesa::reversal('600981','OEI2AK4Q16', 500, 'Wrong Payment','https://test.test/result','https://test.test/timeout', 'C2B');

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;
```

## B2B API

This API is useful when you want to make `business to business payments`. It is, however, not publicly available in the documentation and you have to contact Safaricom and give them a valid usecase/reason on how you plan to use the API for them to give you access to the API.

Before using this method, ensure you have added the `initiator_name`,`initiator_password`,`b2c_shortcode`,`b2b_result_url` and `b2b_timeout_url` configurations to the `config/mpesa.php` file.

The callbacks can be passed as parameters when calling this function.

To use this API you need to call `b2b()` method on the `Mpesa` facade. This function accept the following parameters

1. `receiver_shortcode` - The shortcode of the recipient
2. `command_id` -The type of transaction being made. Can be `BusinessPayBill`, `MerchantToMerchantTransfer`, `MerchantTransferFromMerchantToWorking`, `MerchantServicesMMFAccountTransfer`, `AgencyFloatAdvance`
3. `amount` - The amount to send to the recipient
4. `remarks` - small decription of the payment being made.
5. `account_number` - Required for `BusinessPaybill` CommandID
6. `shortCodeType` - The type of shortcode. Can be `C2B`, `B2C` or `B2B`. Default is `B2B`

```php
use Iankumu\Mpesa\Facades\Mpesa;

$response = Mpesa::b2b($receiver_shortcode, $command_id, $amount, $remarks, $account_number = null,$b2b_result_url = null, $b2b_timeout_url = null, $shortCodeType = 'B2B');

/** @var \Illuminate\Http\Client\Response $response */
$result = $response->json();
return $result;
```
