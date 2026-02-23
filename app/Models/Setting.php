<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

class Setting extends Model
{
    use HasFactory;

    protected static array $resolved = [];

    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public static function getValue(string $key, $default = null)
    {
        if (array_key_exists($key, static::$resolved)) {
            return static::$resolved[$key];
        }

        $cacheKey = "settings:value:{$key}";
        $ttl = now()->addMinutes((int) config('pos.cache.settings_ttl_minutes', 60));

        try {
            $value = Cache::remember($cacheKey, $ttl, function () use ($key, $default) {
                $setting = static::query()->where('key', $key)->first();

                return $setting->value ?? $default;
            });
        } catch (Throwable) {
            return static::$resolved[$key] = $default;
        }

        return static::$resolved[$key] = $value;
    }

    public static function forgetValue(string $key): void
    {
        Cache::forget("settings:value:{$key}");
        unset(static::$resolved[$key]);
    }
}
