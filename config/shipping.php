<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shipping currency
    |--------------------------------------------------------------------------
    */

    'currency' => 'USD',

    /*
    |--------------------------------------------------------------------------
    | Default shipping method
    |--------------------------------------------------------------------------
    */

    'default' => 'standard',

    /*
    |--------------------------------------------------------------------------
    | Available shipping methods
    |--------------------------------------------------------------------------
    */

    'methods' => [

        'economy' => [
            'name' => 'Economy Shipping',
            'price' => 4.99,
            'delivery_time' => '5–8 business days',
            'description' => 'Affordable delivery for non-urgent orders.',
        ],

        'standard' => [
            'name' => 'Standard Shipping',
            'price' => 9.99,
            'delivery_time' => '3–5 business days',
            'description' => 'Reliable standard delivery.',
        ],

        'express' => [
            'name' => 'Express Shipping',
            'price' => 19.99,
            'delivery_time' => '1–2 business days',
            'description' => 'Fast delivery for urgent orders.',
        ],

    ],

];