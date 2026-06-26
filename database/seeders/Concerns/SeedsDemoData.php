<?php

namespace Database\Seeders\Concerns;

trait SeedsDemoData
{
    protected function shouldSeedDemoData(): bool
    {
        $value = env('SEED_DEMO_DATA');

        if ($value !== null) {
            return filter_var($value, FILTER_VALIDATE_BOOL);
        }

        return app()->environment(['local', 'testing']);
    }
}
