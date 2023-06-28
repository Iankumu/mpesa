<?php

return [
    //This is the mpesa environment.Can be sanbox or production
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),

    /*-----------------------------------------
        |The Mpesa Consumer Key
        |------------------------------------------
        */
    'mpesa_consumer_key' => env('MPESA_CONSUMER_KEY'),

    /*-----------------------------------------
        |The Mpesa Consumer Secret
        |------------------------------------------
        */
    'mpesa_consumer_secret' => env('MPESA_CONSUMER_SECRET'),

    /*-----------------------------------------
        |The Lipa na Mpesa Online Passkey
        |------------------------------------------
        */
    'passkey' => env('SAFARICOM_PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'),

    /*-----------------------------------------
        |The Lipa na Mpesa Online ShortCode
        |------------------------------------------
        */
    'shortcode' => env('MPESA_BUSINESS_SHORTCODE', '174379'),

    /*-----------------------------------------
        |The Mpesa Initator Name
        |------------------------------------------
        */
    'initiator_name' => env('MPESA_INITIATOR_NAME', 'testapi'),

    /*-----------------------------------------
        |The Mpesa Initator Password
        |------------------------------------------
        */
    'initiator_password' => env('MPESA_INITIATOR_PASSWORD'),

    /*-----------------------------------------
        |Mpesa B2C ShortCode
        |------------------------------------------
        */
    'b2c_shortcode' => env('MPESA_B2C_SHORTCODE'),

    /*-----------------------------------------
        |Mpesa C2B Validation url
        |------------------------------------------
        */
    'c2b_validation_url' => env('MPESA_C2B_VALIDATION_URL'),

    /*-----------------------------------------
        |Mpesa C2B Confirmation url
        |------------------------------------------
        */
    'c2b_confirmation_url' => env('MPESA_C2B_CONFIRMATION_URL'),

    /*-----------------------------------------
        |Mpesa B2C Result url
        |------------------------------------------
        */
    'b2c_result_url' => env('MPESA_B2C_RESULT_URL'),

    /*-----------------------------------------
        |Mpesa B2C Timeout url
        |------------------------------------------
        */
    'b2c_timeout_url' => env('MPESA_B2C_TIMEOUT_URL'),

    /*-----------------------------------------
        |Mpesa Lipa Na Mpesa callback url
        |------------------------------------------
        */
    'callback_url' => env('MPESA_CALLBACK_URL'),

    /*-----------------------------------------
        |Mpesa Transaction Status Result url
        |------------------------------------------
        */
    'status_result_url' => env('MPESA_STATUS_RESULT_URL'),

    /*-----------------------------------------
        |Mpesa Transaction Status Timeout url
        |------------------------------------------
        */
    'status_timeout_url' => env('MPESA_STATUS_TIMEOUT_URL'),

    /*-----------------------------------------
        |Mpesa Account Balance Result url
        |------------------------------------------
        */
    'balance_result_url' => env('MPESA_BALANCE_RESULT_URL'),

    /*-----------------------------------------
        |Mpesa Account Balance Timeout url
        |------------------------------------------
        */
    'balance_timeout_url' => env('MPESA_BALANCE_TIMEOUT_URL'),

    /*-----------------------------------------
        |Mpesa Reversal Result url
        |------------------------------------------
        */
    'reversal_result_url' => env('MPESA_REVERSAL_RESULT_URL'),

    /*-----------------------------------------
        |Mpesa Reversal Timeout url
        |------------------------------------------
        */
    'reversal_timeout_url' => env('MPESA_REVERSAL_TIMEOUT_URL'),

    /*-----------------------------------------
        |Mpesa B2B urls
        |------------------------------------------
    */
    'b2b_result_url' => env('MPESA_B2B_RESULT_URL'),

    'b2b_timeout_url' => env('MPESA_B2B_TIMEOUT_URL'),
];
