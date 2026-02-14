<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Moyasar API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Moyasar payment gateway integration
    |
    */

    'api_key' => env('MAYSAR_API_KEY'),
    'base_url' => env('MAYSAR_BASE_URL', 'https://api.moyasar.com'),
    'timeout' => env('MAYSAR_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    */
    'endpoints' => [
        'refund' => '/payments/:id/refund',
        'payment' => '/payments/:id',
        'payments' => '/payments',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Statuses
    |--------------------------------------------------------------------------
    */
    'statuses' => [
        'initiated' => 'initiated',
        'paid' => 'paid',
        'authorized' => 'authorized',
        'failed' => 'failed',
        'refunded' => 'refunded',
        'captured' => 'captured',
        'voided' => 'voided',
        'verified' => 'verified',
    ],
];
