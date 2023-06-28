![Laravel Mpesa Package](https://www.iankumu.com/blog/wp-content/uploads/2023/01/Laravel-Mpesa-Banner.png)

# Mpesa Package for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/Iankumu/mpesa.svg?style=flat-square)](https://packagist.org/packages/Iankumu/mpesa)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/Iankumu/mpesa/ci.yml?branch=main&label=tests&style=flat-square)](https://github.com/Iankumu/mpesa/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/Iankumu/mpesa.svg?style=flat-square)](https://packagist.org/packages/Iankumu/mpesa)

A Laravel package for the [Mpesa Daraja Apis](https://developer.safaricom.co.ke/APIs).This package allows you to integrate the Mpesa Daraja Apis into your Laravel Application with ease.

The package contains implementation of the following Daraja APIs

- Lipa na Mpesa Express Online(STKPUSH)
- Business to Customer(B2C)
- Business to Business(B2B)
- Customer to Business(C2B)
- Transaction status
- Account Balance
- Reversals

## Installation

You can install this package through composer

```bash
composer require iankumu/mpesa
```

## Configuration

The next step is to publish the application configurations. Run the following command

```bash
php artisan mpesa:install
```

This will publish the `config/mpesa.php` file which contains the mpesa configurations for your applications. These credentials are provided on your [Safaricom Developer Portal](https://developer.safaricom.co.ke/).

You also need to specify the application's environment; either `sandbox` or `production` so that the relevant api url endpoints are called.

```php
// config/mpesa.php
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

```

For security reasons you need to define your API credentials in `.env` file.

You are free to customise the configuration values to your liking. An example would be to set the callback urls directly to the config file as opposed to adding them to the `.env` file.

### Usage

Code examples and usage can be found in [Usage.md](USAGE.md)

If you prefer a step by step tutorial, you can read the article I wrote on [How to integrate Mpesa into your Laravel Application](https://www.iankumu.com/blog/laravel-mpesa).

You can also find a [demo application](https://github.com/Iankumu/Payments) I created that uses the package. It provides a starting point on how to integrate Mpesa into a Laravel Application.

### Testing

```bash
composer test
```

### Contributing

Thank you for considering contributing to Laravel Mpesa! All contributions and PR are welcome. You can read the contribution guide [here](CONTRIBUTING.md).

### Credits

- [Ian Kumu](https://github.com/IanKumu)
- [Mugwanjira Maina](https://github.com/maina401)
- [All Contributors](../../contributors)

### License

This package is an open-sourced software licensed under the [MIT license](LICENSE.md).
