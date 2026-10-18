<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'ecommerce_storefronts',
        'ecommerce_catalog_products',
        'ecommerce_customer_profiles',
        'ecommerce_checkout_orders',
    ];

    public function up(): void
    {
        // Una instalación nueva ya crea company_id; SQLite de pruebas también.
        if (! Schema::hasTable('ecommerce_storefronts') || ! Schema::hasColumn('ecommerce_storefronts', 'tenant_uuid')) {
            return;
        }

        $tenantUuids = collect($this->tables)
            ->filter(fn (string $table) => Schema::hasTable($table))
            ->flatMap(fn (string $table) => DB::table($table)->pluck('tenant_uuid'))
            ->filter()
            ->unique()
            ->values();

        $companyIds = $tenantUuids->isEmpty()
            ? collect()
            : DB::connection('administracion')->table('companies')
                ->whereIn('tenant_uuid', $tenantUuids)
                ->pluck('id', 'tenant_uuid');

        $missing = $tenantUuids->diff($companyIds->keys());
        if ($missing->isNotEmpty()) {
            throw new RuntimeException('No se puede migrar E-commerce: faltan compañías en Administración para: '.$missing->implode(', '));
        }

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_uuid')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->unsignedBigInteger('company_id')->nullable()->after('id');
            });

            foreach ($companyIds as $tenantUuid => $companyId) {
                DB::table($table)->where('tenant_uuid', $tenantUuid)->update(['company_id' => $companyId]);
            }

            if (DB::table($table)->whereNull('company_id')->exists()) {
                throw new RuntimeException("No se pudo resolver company_id en {$table}.");
            }

            $this->dropIndexesForColumn($table, 'tenant_uuid');
            DB::statement("ALTER TABLE `{$table}` DROP COLUMN `tenant_uuid`, MODIFY `company_id` BIGINT UNSIGNED NOT NULL");
        }

        Schema::table('ecommerce_storefronts', fn (Blueprint $table) => $table->index('company_id'));
        Schema::table('ecommerce_catalog_products', fn (Blueprint $table) => $table->unique(['company_id', 'pos_product_id']));
        Schema::table('ecommerce_customer_profiles', fn (Blueprint $table) => $table->unique(['company_id', 'keycloak_subject']));
        Schema::table('ecommerce_checkout_orders', fn (Blueprint $table) => $table->index(['company_id', 'status']));
    }

    public function down(): void
    {
        // No se restaura tenant_uuid: Administración es la fuente única de la identidad de empresa.
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
