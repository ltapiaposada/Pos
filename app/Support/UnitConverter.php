<?php

namespace App\Support;

class UnitConverter
{
    private const GRAMS_PER_UNIT = [
        'g' => 1.0,
        'kg' => 1000.0,
        'libra' => 500.0,
    ];

    public static function factor(?string $fromUnit, ?string $toUnit): ?float
    {
        $fromUnit = self::normalize($fromUnit);
        $toUnit = self::normalize($toUnit);

        if ($fromUnit === null || $toUnit === null) {
            return null;
        }

        if ($fromUnit === $toUnit) {
            return 1.0;
        }

        if (! isset(self::GRAMS_PER_UNIT[$fromUnit], self::GRAMS_PER_UNIT[$toUnit])) {
            return null;
        }

        return self::GRAMS_PER_UNIT[$fromUnit] / self::GRAMS_PER_UNIT[$toUnit];
    }

    public static function resolveFactor(?string $fromUnit, ?string $toUnit, float $fallback = 1.0): float
    {
        return self::factor($fromUnit, $toUnit) ?? $fallback;
    }

    private static function normalize(?string $unit): ?string
    {
        if (! is_string($unit) || trim($unit) === '') {
            return null;
        }

        return mb_strtolower(trim($unit));
    }
}
