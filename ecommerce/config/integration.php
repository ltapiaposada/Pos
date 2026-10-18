<?php

return [
    'internal_token' => env('INTERNAL_API_TOKEN'),
    'pos_url' => rtrim((string) env('POS_API_URL', 'http://localhost:8000'), '/'),
];
