<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $companyTables = ['subscriptions', 'entitlements', 'tenant_domains', 'provisioning_jobs'];

    public function up(): void
    {
        if (! Schema::hasTable('tenants')) {
            return;
        }

        foreach ($this->companyTables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            $this->dropTenantForeignKeys($table);
            $this->dropIndexesForColumn($table, 'tenant_id');
            DB::statement("ALTER TABLE `{$table}` CHANGE `tenant_id` `company_id` BIGINT UNSIGNED NOT NULL");
        }

        Schema::rename('tenants', 'companies');

        Schema::table('companies', function (Blueprint $table): void {
            if (! Schema::hasColumn('companies', 'domain')) {
                $table->string('domain')->nullable()->unique()->after('name');
            }
            if (! Schema::hasColumn('companies', 'company_type_id')) {
                // Es un valor operativo heredado del POS; no existe FK entre proyectos.
                $table->unsignedBigInteger('company_type_id')->nullable()->after('address');
            }
            if (! Schema::hasColumn('companies', 'kickoff_completed')) {
                $table->boolean('kickoff_completed')->default(false)->after('status');
            }
        });

        Schema::table('subscriptions', fn (Blueprint $table) => $table->index(['company_id', 'status', 'ends_at']));
        Schema::table('entitlements', fn (Blueprint $table) => $table->unique(['company_id', 'service']));
        Schema::table('tenant_domains', fn (Blueprint $table) => $table->index('company_id'));
        Schema::table('provisioning_jobs', fn (Blueprint $table) => $table->index('company_id'));
    }

    public function down(): void
    {
        // La administración ya es la fuente de verdad; no se revierte automáticamente.
    }

    private function dropTenantForeignKeys(string $table): void
    {
        $constraints = DB::select(
            'SELECT DISTINCT constraint_name FROM information_schema.key_column_usage WHERE table_schema = DATABASE() AND table_name = ? AND referenced_table_name = ?',
            [$table, 'tenants']
        );

        foreach ($constraints as $constraint) {
            $name = str_replace('`', '``', (string) array_values((array) $constraint)[0]);
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$name}`");
        }
    }

    private function dropIndexesForColumn(string $table, string $column): void
    {
        $indexes = DB::select(
            'SELECT DISTINCT index_name FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? AND index_name <> ?',
            [$table, $column, 'PRIMARY']
        );

        foreach ($indexes as $index) {
            $name = str_replace('`', '``', (string) array_values((array) $index)[0]);
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
        }
    }
};
