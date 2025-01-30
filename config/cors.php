<?php

return [

    'paths' => ['api/*', 'storage/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3000'], // Specify your frontend URL here
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];




