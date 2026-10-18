<?php

return [
    'internal_token' => env('INTERNAL_API_TOKEN'),
    'pos_url' => rtrim((string) env('POS_API_URL', 'http://localhost:8000'), '/'),
    'ecommerce_url' => rtrim((string) env('ECOMMERCE_API_URL', 'http://localhost:8001'), '/'),
];
