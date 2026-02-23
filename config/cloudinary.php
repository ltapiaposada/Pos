<?php

return [
    'url' => env('CLOUDINARY_URL'),
    'folder' => env('CLOUDINARY_FOLDER', 'pos'),
    'verify_ssl' => env('CLOUDINARY_VERIFY_SSL', true),
];
