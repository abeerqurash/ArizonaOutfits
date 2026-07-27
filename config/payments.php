<?php

return [
    'currency' => env(
        'PAYMENT_CURRENCY',
        'USD'
    ),

    'stripe' => [
        'enabled' => env(
            'STRIPE_ENABLED',
            true
        ),

        'key' => env('STRIPE_KEY'),

        'secret' => env(
            'STRIPE_SECRET'
        ),

        'webhook_secret' => env(
            'STRIPE_WEBHOOK_SECRET'
        ),
    ],

    // 'paypal' => [
    //     'enabled' => env(
    //         'PAYPAL_ENABLED',
    //         true
    //     ),

    //     'mode' => env(
    //         'PAYPAL_MODE',
    //         'sandbox'
    //     ),

    //     'client_id' => env(
    //         'PAYPAL_CLIENT_ID'
    //     ),

    //     'client_secret' => env(
    //         'PAYPAL_CLIENT_SECRET'
    //     ),

    //     'webhook_id' => env(
    //         'PAYPAL_WEBHOOK_ID'
    //     ),
    // ],

    // 'cod' => [
    //     'enabled' => env(
    //         'COD_ENABLED',
    //         true
    //     ),
    // ],

    'bank_transfer' => [
        'enabled' => env(
            'BANK_TRANSFER_ENABLED',
            true
        ),

        'bank_name' => env(
            'BANK_NAME'
        ),

        'account_name' => env(
            'BANK_ACCOUNT_NAME'
        ),

        'account_number' => env(
            'BANK_ACCOUNT_NUMBER'
        ),

        'iban' => env(
            'BANK_IBAN'
        ),

        'swift_code' => env(
            'BANK_SWIFT_CODE'
        ),
    ],
];