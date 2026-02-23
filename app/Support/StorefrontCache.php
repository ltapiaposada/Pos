<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class StorefrontCache
{
    public const PRODUCTS_VERSION_KEY = 'storefront:products:version';

    public static function bumpProductsVersion(): void
    {
        $current = (int) Cache::get(self::PRODUCTS_VERSION_KEY, 1);
        Cache::forever(self::PRODUCTS_VERSION_KEY, $current + 1);
    }
}
