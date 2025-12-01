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
    'azure' => [
        'tenant_id' => env('AZURE_TENANT_ID'),
        'client_id' => env('AZURE_CLIENT_ID'), // Para validar que el token sea para ESTA app (aud)
        'allowed_azp' => env('AZURE_ALLOWED_AZP'), // Para validar clientes autorizados
    ],
    
    'passport' => [
        'public_key' => storage_path('oauth-public.key'), // La clave que copiaste del Auth Service
    ],

    'kafka' => [
        'brokers' => env('KAFKA_BROKERS'),
        'client_id' => env('KAFKA_CLIENT_ID'),
        'security_protocol' => env('KAFKA_SECURITY_PROTOCOL'),
        'sasl_username' => env('KAFKA_SASL_USERNAME'),
        'sasl_password' => env('KAFKA_SASL_PASSWORD'),
        'sasl_mechanisms' => env('KAFKA_SASL_MECHANISMS'),
        'debug' => env('KAFKA_DEBUG'),
        'e2e' => env('KAFKA_E2E'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
