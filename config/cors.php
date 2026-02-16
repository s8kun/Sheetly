<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // 1. خلي هذي فاضية تماماً (عشان ما يصير تضارب)
    'allowed_origins' => [],

    // 2. حط النجمة هنا بس!
    // هذا يعني: "أي رابط يجي، اقبله ورجع اسمه في الهيدر"
    'allowed_origins_patterns' => ['*'],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // 3. ضروري true عشان Sanctum يشتغل
    'supports_credentials' => true,
];
