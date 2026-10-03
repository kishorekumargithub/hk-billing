<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Usage API Rate Limit
    |--------------------------------------------------------------------------
    |
    | Maximum number of usage requests allowed per minute per merchant.
    |
    */
    'usage_rate_limit' => (int) env('USAGE_RATE_LIMIT', 120),
];