<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paths que permitirán CORS
    |--------------------------------------------------------------------------
    */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
    |--------------------------------------------------------------------------
    | Métodos permitidos
    |--------------------------------------------------------------------------
    */
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    /*
    |--------------------------------------------------------------------------
    | Orígenes permitidos
    |--------------------------------------------------------------------------
    */
    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://localhost:8080',
        'http://localhost',
        'https://localhost',
        'capacitor://localhost',
        'ionic://localhost',
        'file://',
        'https://hospital-frontend-taupe.vercel.app',
        'https://hospital-frontend-dtj180a2u.vercel.app',
    ],

    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Encabezados permitidos
    |--------------------------------------------------------------------------
    */
    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'Accept',
        'Origin',
        'X-CSRF-TOKEN',
    ],

    /*
    |--------------------------------------------------------------------------
    | Exponer encabezados
    |--------------------------------------------------------------------------
    */
    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Permitir credenciales (cookies)
    |--------------------------------------------------------------------------
    */
    'supports_credentials' => true,
];
