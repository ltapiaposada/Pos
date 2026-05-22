<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Support\CompanyContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

class Setting extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected static array $resolved = [];

    protected $fillable = [
        'company_id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public static function getValue(string $key, $default = null, ?int $companyId = null)
    {
        $companyId ??= CompanyContext::companyIdForScopedReads();
        $resolvedKey = ($companyId ?? 'global').':'.$key;

        if (array_key_exists($resolvedKey, static::$resolved)) {
            return static::$resolved[$resolvedKey];
        }

        $cacheKey = "settings:company:".($companyId ?? 'global').":value:{$key}";
        $ttl = now()->addMinutes((int) config('pos.cache.settings_ttl_minutes', 60));

        try {
            $value = Cache::remember($cacheKey, $ttl, function () use ($companyId, $key, $default) {
                $setting = static::query()
                    ->when($companyId !== null, fn ($query) => $query->where('company_id', $companyId))
                    ->where('key', $key)
                    ->first();

                return $setting->value ?? $default;
            });
        } catch (Throwable) {
            return static::$resolved[$resolvedKey] = $default;
        }

        return static::$resolved[$resolvedKey] = $value;
    }

    public static function forgetValue(string $key, ?int $companyId = null): void
    {
        $companyId ??= CompanyContext::companyIdForScopedReads();
        Cache::forget("settings:company:".($companyId ?? 'global').":value:{$key}");
        unset(static::$resolved[($companyId ?? 'global').':'.$key]);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
