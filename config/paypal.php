<?php

// return [
//     'base_url'      => 'https://api-m.sandbox.paypal.com',
//     'mode'          => 'sandbox',
//     'client_id'     => env('PAYPAL_CLIENT_ID'),
//     'client_secret' => env('PAYPAL_CLIENT_SECRET'),
//     'currency'      => env('PAYPAL_CURRENCY', 'GBP'),
//     'plan_id'       => env('PAYPAL_PLAN_ID'),
// ];
// return [
//     'base_url' => env('PAYPAL_BASE_URL'),
//     'client_id' => env('PAYPAL_CLIENT_ID'),
//     'secret' => env('PAYPAL_SECRET'),
// ];
return [
    'client_id' => env('PAYPAL_CLIENT_ID'),
    'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    'mode' => env('PAYPAL_MODE', 'sandbox'),
];
