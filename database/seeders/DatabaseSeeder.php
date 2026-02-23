<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            KickoffSeeder::class,
            TaxSeeder::class,
            ProductSeeder::class,
            ProductTypeDemoSeeder::class,
            CustomerSeeder::class,
            InventorySeeder::class,
            SettingsSeeder::class,
            AccountingSeeder::class,
        ]);

        $this->syncPostgresSequences();
    }

    protected function syncPostgresSequences(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $tables = DB::select("
            select table_name
            from information_schema.columns
            where table_schema = 'public'
              and column_name = 'id'
              and column_default like 'nextval(%'
        ");

        foreach ($tables as $table) {
            $tableName = $table->table_name;
            $sequence = DB::selectOne(
                "select pg_get_serial_sequence('public.{$tableName}', 'id') as seq"
            );

            if (! $sequence || ! $sequence->seq) {
                continue;
            }

            $maxId = (int) (DB::table($tableName)->max('id') ?? 0);
            $next = $maxId > 0 ? $maxId : 1;
            DB::statement("select setval('{$sequence->seq}', {$next}, true)");
        }
    }
}
