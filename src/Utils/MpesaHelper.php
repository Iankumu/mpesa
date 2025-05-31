<?php

namespace Iankumu\Mpesa\Utils;

use Iankumu\Mpesa\Exceptions\CallbackException;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

trait MpesaHelper
{
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
    }

    // Generate an AccessToken using the Consumer Key and Consumer Secret
    public function generateAccessToken()
    {
        $consumer_key = $this->getConfig('mpesa_consumer_key');
        $consumer_secret = $this->getConfig('mpesa_consumer_secret');

        $url = $this->url . '/oauth/v1/generate?grant_type=client_credentials';

        $response = Http::withBasicAuth($consumer_key, $consumer_secret)
            ->get($url);

        $result = json_decode($response);

        return data_get($result, 'access_token');
    }

    // Common Format Of The Mpesa APIs.
    public function MpesaRequest($url, $body)
    {

        $response = Http::withToken($this->generateAccessToken())
            ->acceptJson()
            ->post($url, $body);

        return $response;
    }

    // Generate a base64  password using the Safaricom PassKey and the Business ShortCode to be used in the Mpesa Transaction
    public function LipaNaMpesaPassword()
    {
        $timestamp = $this->getFormattedTimeStamp();
        return base64_encode($this->getConfig('shortcode') . $this->getConfig('passkey') . $timestamp);
    }

    public function phoneValidator($phoneno)
    {
        // Some validations for the phonenumber to format it to the required format
        $phoneno = (substr($phoneno, 0, 1) == '+') ? str_replace('+', '', $phoneno) : $phoneno;
        $phoneno = (substr($phoneno, 0, 1) == '0') ? preg_replace('/^0/', '254', $phoneno) : $phoneno;
        $phoneno = (substr($phoneno, 0, 1) == '7') ? "254{$phoneno}" : $phoneno;

        return $phoneno;
    }

    public function getFormattedTimeStamp()
    {
        $timestamp = Carbon::rawParse('now')->format('YmdHis');

        return $timestamp;
    }

    public function generate_security_credential()
    {
        if ($this->getConfig('environment') == 'sandbox') {
            $pubkey = File::get(__DIR__ . '/../certificates/SandboxCertificate.cer');
        } else {
            $pubkey = File::get(__DIR__ . '/../certificates/ProductionCertificate.cer');
        }
        openssl_public_encrypt($this->getConfig('initiator_password'), $output, $pubkey, OPENSSL_PKCS1_PADDING);

        return base64_encode($output);
    }

    public function validationResponse($result_code, $result_description)
    {
        $result = json_encode([
            'ResultCode' => $result_code,
            'ResultDesc' => $result_description,
        ]);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->setContent($result);

        return $response;
    }

    public function getConfig($key, $default = null)
    {
        return config('mpesa.' . $key, $default);
    }

    public function resolveCallbackUrl($paramUrl, $configUrlKey, $exceptionMessageKey)
    {
        $callbacks = $this->getConfig('callbacks');

        $configUrl = $callbacks[$configUrlKey] ?? null;

        if ($paramUrl !== null) {
            return $paramUrl;
        } elseif ($configUrl !== null) {
            return $configUrl;
        } else {
            throw CallbackException::make(
                $exceptionMessageKey,
                'Ensure you have set the ' . str_replace('_', ' ', $exceptionMessageKey) . ' in the mpesa config file or passed as a parameter'
            );
        }
    }
}
