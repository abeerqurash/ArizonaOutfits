<?php

return [
    // Existing catalog costs/prices were displayed in GBP. Set this explicitly if your catalog uses another currency.
    'currency' => strtoupper(env('INVENTORY_CURRENCY', 'GBP')),


    /*
    |--------------------------------------------------------------------------
    | Low-stock threshold
    |--------------------------------------------------------------------------
    |
    | An alert is created when stock becomes equal to or lower than this
    | value. Stock equal to zero creates an out-of-stock alert.
    |
    */

    'low_stock_threshold' => (int) env(
        'LOW_STOCK_THRESHOLD',
        5
    ),

    /*
    |--------------------------------------------------------------------------
    | Inventory alert email
    |--------------------------------------------------------------------------
    |
    | Inventory alerts are sent to this email address. When this value is
    | empty, the admin order email from config/mail.php is used instead.
    |
    */

    'alert_email' => env(
        'INVENTORY_ALERT_EMAIL'
    ),

];
