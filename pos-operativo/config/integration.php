<?php

return [
    'internal_token' => env('INTERNAL_API_TOKEN'),
    'outbox_signing_secret' => env('OUTBOX_SIGNING_SECRET'),
    'ecommerce_url' => rtrim((string) env('ECOMMERCE_API_URL', 'http://localhost:8001'), '/'),
];
