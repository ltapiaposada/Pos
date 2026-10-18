<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->preventUnsafeTestingDatabase();
    }

    private function preventUnsafeTestingDatabase(): void
    {
        if (! app()->environment('testing')) {
            return;
        }

        $default = (string) config('database.default');
        $database = (string) config("database.connections.{$default}.database");

        if ($default === 'sqlite' && $database === ':memory:') {
            return;
        }

        if ($database === '') {
            throw new RuntimeException('Testing DB is empty. Configure .env.testing with a dedicated database.');
        }

        $forbidden = ['postgres', 'pos', 'production'];
        $isTestingName = str_ends_with($database, '_testing')
            || str_ends_with($database, '_test')
            || $database === 'testing';

        if (in_array($database, $forbidden, true) || ! $isTestingName) {
            throw new RuntimeException(
                "Unsafe testing database [{$database}]. Use a dedicated DB name ending in _testing or _test."
            );
        }
    }
}
