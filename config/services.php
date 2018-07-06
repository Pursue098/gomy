<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('pk_test_O98B7YHkF2paoJEBmJRpIffv'),
        'secret' => env('sk_test_qm78rff8EHmwPDNRqceBiaKA'),
    ],

    'braintree' => [
        'model'  => App\User::class,
        'environment' => env('BRAINTREE_ENV'),
        'merchant_id' => env('BRAINTREE_MERCHANT_ID'),
        'public_key' => env('BRAINTREE_PUBLIC_KEY'),
        'private_key' => env('BRAINTREE_PRIVATE_KEY'),
    ],

    'twilio' => [
        'username'    => env('TWILIO_USERNAME'), // optional when using auth token
        'password'    => env('TWILIO_PASSWORD'), // optional when using auth token
        'auth_token'  => env('TWILIO_AUTH_TOKEN'), // optional when using username and password
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'from'        => env('TWILIO_FROM'), // optional
    ],
];
