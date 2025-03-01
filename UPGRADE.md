# Upgrade Guide

## Upgrading from 1.* to 2.0.0

Version 2.0 of the package introduces significant changes, focused on consolidating configuration and dropping support for older PHP and Laravel versions. Please carefully follow these instructions to upgrade your application.

### Key Changes in 2.0:

**1. Consolidated Callback Configuration:**
    Individual callback URL properties (e.g., MPESA_CALLBACK_URL, MPESA_B2C_RESULT_URL) have been replaced with a single `mpesa.callbacks` array in `config/mpesa.php`.
**2. Dropped PHP 8.0 and 8.1 Support:**
    The package now requires PHP 8.2 or 8.3.
**3. Dropped Laravel 8 and 9 Support:**
    The package now supports Laravel 10, 11, and 12.
**4. Passing Callback URLs as Parameters**

### Passing Callback URLs as Parameters
In version 2.0, you can now pass callback URLs directly as parameters to the methods in the `Mpesa` Facade.

**How it Works:**

* If a callback URL is passed as a parameter, it will be used.
* If a callback URL is not passed as a parameter, the value from the `mpesa.callbacks` array in `config/mpesa.php` will be used.

**Example (STK Push):**

```php
use Iankumu\Mpesa\Facades\Mpesa;

$phoneNumber = '2547XXXXXXXX';
$amount = 100;
$accountNumber = '12345';
$callbackUrl = 'https://test.test/callback';

$response = Mpesa::stkpush($phoneNumber, $amount, $accountNumber, $callbackUrl);

// Or, if you want to use the configured callback URL:
$response = Mpesa::stkpush($phoneNumber, $amount, $accountNumber);

```


### Upgrade Steps
**1. Update `composer.json`:**

- Update the `iankumu/mpesa` package version in your composer.json file:

```json
"require": {
    "iankumu/mpesa": "^2.0",
    // ... other dependencies
}
```
- Ensure your `require` section reflects the new PHP and Laravel version requirements:
```json
"require": {
    "php": "^8.2",
    "illuminate/support": "^10.0 | ^11.0 | ^12.0",
    "illuminate/http": "^10.0 | ^11.0 | ^12.0",
    "guzzlehttp/guzzle": "^7.5",
    // ... other dependencies
}
```
- Run `composer update` to install the new package version and update dependencies.

**2. Update Configuration (`config/mpesa.php`):**
- Migrate Callback URLs:

  - Open your `config/mpesa.php` file.
  - Locate the individual callback URL properties (e.g., `callback_url`, `b2c_result_url`).
  - Move these URLs into the callbacks array, using the appropriate keys:
  Example:

  **Old Configuration (1.x):**
  ```php
  // config/mpesa.php
    'callback_url' => env('MPESA_CALLBACK_URL'),
    'b2c_result_url' => env('MPESA_B2C_RESULT_URL'),
   // ...
  ```

  **New Configuration (2.0):**
  ```php
  // config/mpesa.php
  'callbacks' => [
      'stk_callback_url' => env('MPESA_STK_CALLBACK_URL'),
      'b2c_result_url' => env('MPESA_B2C_RESULT_URL'),
      // ... other callbacks
  ],
  ```

**3. Update Your Code:**
- If you have any code that directly accessed the old callback configuration keys, update it to use the new `mpesa.callbacks` array.
  Example:

  **Old Code (1.x):**
  ```php
  $callbackUrl = config('mpesa.callback_url');
  ```

  **New Code (2.0):**
  ```php
  $callbackUrl = config('mpesa.callbacks.stk_callback_url');
  ```

**4. Update Your `.env` file**
- Update your .env file to match the new callback configuration keys if need be.

**5. Update Your Server Environment:**
- Ensure your server is running PHP 8.2 or greater.
- If you're using Laravel 8 or 9, you'll need to upgrade to Laravel 10, 11, or 12.


