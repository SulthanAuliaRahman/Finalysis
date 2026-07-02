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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'python_service' => [
        'url'     => env('PYTHON_SERVICE_URL', 'http://localhost:8000'),
        'timeout' => env('PYTHON_SERVICE_TIMEOUT', 120),
    ],

    'reranker' => [
        'provider'       => env('RERANKER_PROVIDER', 'localai'),
        'model'          => env('RERANKER_MODEL'), // sengaja null; default per-provider ditentukan di RagAgent::buildReranker()
        'top_n'          => env('RERANKER_TOP_N', 3),
        'cohere_api_key' => env('COHERE_API_KEY'),
        'jina_api_key'   => env('JINA_API_KEY'),
        'localai_url'    => env('LOCALAI_URL', 'http://localhost:8080/'),
    ],

];
