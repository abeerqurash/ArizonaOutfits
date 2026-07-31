<?php

return [
    'currency' => strtoupper(
        env(
            'PAYMENT_CURRENCY',
            'USD'
        )
    ),

    'stripe' => [
        'enabled' => env(
            'STRIPE_ENABLED',
            false
        ),

        'key' => env(
            'STRIPE_KEY'
        ),

        'secret' => env(
            'STRIPE_SECRET'
        ),

        'webhook_secret' => env(
            'STRIPE_WEBHOOK_SECRET'
        ),
    ],

    'bank_transfer' => [
        'enabled' => env(
            'BANK_TRANSFER_ENABLED',
            false
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