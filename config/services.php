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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Toad (The Other Application Data)
    |--------------------------------------------------------------------------
    |
    | Configuration de l'API externe de gestion de vidéothèque.
    | Les variables sont définies dans le fichier .env du projet.
    |
    | url        : URL de l'API locale (ex: http://localhost:8180)
    | url_remote : URL de l'API distante (ex: http://rftg.mtb111.com)
    | token      : Token Bearer statique pour l'authentification (dev)
    | jwt_*      : Paramètres pour la génération de JWT côté PHP
    |
    */
    'toad' => [
        'url'            => env('TOAD_API_URL') . ':' . env('TOAD_API_PORT'),
        'url_remote'     => env('TOAD_API_URL_REMOTE', 'http://rftg.mtb111.com'),
        'token'          => env('TOAD_API_TOKEN'),
        'jwt_secret'     => env('TOAD_CLIENT_JWT_SECRET'),
        'jwt_iss'        => env('TOAD_CLIENT_JWT_ISS', 'mario-app'),
        'jwt_aud'        => env('TOAD_CLIENT_JWT_AUD', 'toad-api'),
        'jwt_ttl'        => env('TOAD_CLIENT_JWT_TTL', 3600),
        'admin_email'    => env('TOAD_ADMIN_EMAIL'),
        'admin_password' => env('TOAD_ADMIN_PASSWORD'),
    ],

];
