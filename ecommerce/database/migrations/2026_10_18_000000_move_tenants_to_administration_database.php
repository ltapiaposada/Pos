<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenants')) {
            return;
        }

        foreach (['storefronts', 'catalog_products', 'customer_profiles', 'checkout_orders'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                if (! Schema::hasColumn($blueprint->getTable(), 'tenant_uuid')) {
                    $blueprint->uuid('tenant_uuid')->nullable()->after('id');
                }
            });

            if (Schema::hasColumn($table, 'tenant_id')) {
                DB::statement("UPDATE `{$table}` AS target INNER JOIN `tenants` AS source ON source.id = target.tenant_id SET target.tenant_uuid = source.tenant_uuid WHERE target.tenant_uuid IS NULL");
            }
        }

        $this->migrateStorefronts();
        $this->migrateCatalogProducts();
        $this->migrateCustomerProfiles();
        $this->migrateCheckoutOrders();

        if (Schema::hasTable('inbound_events')) {
            Schema::rename('inbound_events', 'ecommerce_inbound_events');
        }

        if ($this->tenantTableHasNoReferences()) {
            Schema::drop('tenants');
        }
    }

    public function down(): void
    {
        // La migración de datos es intencionalmente irreversible.
    }

    private function migrateStorefronts(): void
    {
        if (! Schema::hasTable('storefronts')) {
            return;
        }

        Schema::table('storefronts', function (Blueprint $table) {
            if ($this->foreignKeyExists('storefronts', 'storefronts_tenant_id_foreign')) {
                $table->dropForeign('storefronts_tenant_id_foreign');
            }
            $table->dropColumn('tenant_id');
            $table->index('tenant_uuid');
        });
        Schema::rename('storefronts', 'ecommerce_storefronts');
    }

    private function migrateCatalogProducts(): void
    {
        if (! Schema::hasTable('catalog_products')) {
            return;
        }

        Schema::table('catalog_products', function (Blueprint $table) {
            if ($this->foreignKeyExists('catalog_products', 'catalog_products_tenant_id_foreign')) {
                $table->dropForeign('catalog_products_tenant_id_foreign');
            }
            foreach (['catalog_products_tenant_id_pos_product_id_unique', 'catalog_products_tenant_product_unique'] as $index) {
                if ($this->indexExists('catalog_products', $index)) {
                    $table->dropUnique($index);
                }
            }
            $table->dropColumn('tenant_id');
            $table->unique(['tenant_uuid', 'pos_product_id']);
        });
        Schema::rename('catalog_products', 'ecommerce_catalog_products');
    }

    private function migrateCustomerProfiles(): void
    {
        if (! Schema::hasTable('customer_profiles')) {
            return;
        }

        Schema::table('customer_profiles', function (Blueprint $table) {
            if ($this->foreignKeyExists('customer_profiles', 'customer_profiles_tenant_id_foreign')) {
                $table->dropForeign('customer_profiles_tenant_id_foreign');
            }
            foreach (['customer_profiles_tenant_id_keycloak_subject_unique', 'customer_profiles_tenant_subject_unique'] as $index) {
                if ($this->indexExists('customer_profiles', $index)) {
                    $table->dropUnique($index);
                }
            }
            $table->dropColumn('tenant_id');
            $table->unique(['tenant_uuid', 'keycloak_subject']);
        });
        Schema::rename('customer_profiles', 'ecommerce_customer_profiles');
    }

    private function migrateCheckoutOrders(): void
    {
        if (! Schema::hasTable('checkout_orders')) {
            return;
        }

        Schema::table('checkout_orders', function (Blueprint $table) {
            if ($this->foreignKeyExists('checkout_orders', 'checkout_orders_tenant_id_foreign')) {
                $table->dropForeign('checkout_orders_tenant_id_foreign');
            }
            if ($this->indexExists('checkout_orders', 'checkout_orders_tenant_id_status_index')) {
                $table->dropIndex('checkout_orders_tenant_id_status_index');
            }
            $table->dropColumn('tenant_id');
            $table->index(['tenant_uuid', 'status']);
        });
        Schema::rename('checkout_orders', 'ecommerce_checkout_orders');
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        return DB::table('information_schema.table_constraints')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('constraint_name', $constraint)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }

    private function tenantTableHasNoReferences(): bool
    {
        return ! DB::table('information_schema.key_column_usage')
            ->where('referenced_table_schema', DB::getDatabaseName())
            ->where('referenced_table_name', 'tenants')
            ->exists();
    }
};
