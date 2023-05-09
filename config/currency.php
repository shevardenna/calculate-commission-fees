<?php

return [
    'api_url' => env('CURRENCY_RATES_API'),
    'default_rate' => [
        'eur' =>  env('DEFAULT_EUR_RATE'),
        'usd' =>  env('DEFAULT_USD_RATE'),
        'jpy' =>  env('DEFAULT_JPY_RATE'),
    ],
];
