<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'key'    => env('STRIPE_KEY'),
    ],


    'web-azure' => [
        'token_url'     => env('AZURE_TOKEN_URL'),
        'client_id'     => env('AZURE_CLIENT_ID'),
        'client_secret' => env('AZURE_CLIENT_SECRET'),
        'scope'         => env('AZURE_SCOPE'),
        'redirect_url'  => env('AZURE_REDIRECT_URL'),
        'tenant_id'     => env('AZURE_TENANT_ID'),
    ],
    'azure' => [
        'token_url'     => env('AZURE_TOKEN_URL'),
        'client_id'     => env('API_AZURE_CLIENT_ID'),
        'client_secret' => env('API_AZURE_CLIENT_SECRET'),
        'scope'         => env('API_AZURE_SCOPE'),
        'redirect_url'  => env('AZURE_REDIRECT_URL'),
        'tenant_id'     => env('AZURE_TENANT_ID'),
    ],

    'safra' => [
        'base_url' => env('SAFRA_API_BASE_URL'),
        'subscription_key' => env('SAFRA_SUBSCRIPTION_KEY'),
        'merchant_id'      => env('SAFRA_MERCHANT_ID'),
        'username'         => env('SAFRA_USERNAME'),
    ],

   
];
