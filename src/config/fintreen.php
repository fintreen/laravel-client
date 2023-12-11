<?php

return [
    // If this one is set to true config will be read from Setting::get('fintreen_token'), Setting::get('fintreen_email')
    'useConfigFromBackpackSettings' => env('FINTREEN_USE_BAKCPACK', ''),
    'isTest' => (bool) env('FINTREEN_TEST_MODE', false),
    'token' =>   env('FINTREEN_TOKEN', ''),
    'email' =>   env('FINTREEN_EMAIL', ''),
];