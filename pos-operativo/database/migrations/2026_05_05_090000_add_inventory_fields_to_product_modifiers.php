<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_modifier_options')) {
            Schema::table('product_modifier_options', function (Blueprint $table) {
                if (! Schema::hasColumn('product_modifier_options', 'inventory_quantity')) {
                    $table->decimal('inventory_quantity', 12, 3)->nullable()->after('product_id');
                }

                if (! Schema::hasColumn('product_modifier_options', 'inventory_unit')) {
                    $table->string('inventory_unit', 32)->nullable()->after('inventory_quantity');
                }

                if (! Schema::hasColumn('product_modifier_options', 'inventory_unit_factor')) {
                    $table->decimal('inventory_unit_factor', 12, 6)->default(1)->after('inventory_unit');
                }
            });
        }

        if (Schema::hasTable('restaurant_order_item_selections')) {
            Schema::table('restaurant_order_item_selections', function (Blueprint $table) {
                if (! Schema::hasColumn('restaurant_order_item_selections', 'inventory_quantity')) {
                    $table->decimal('inventory_quantity', 12, 3)->default(0)->after('price_delta');
                }

                if (! Schema::hasColumn('restaurant_order_item_selections', 'inventory_unit')) {
                    $table->string('inventory_unit', 32)->nullable()->after('inventory_quantity');
                }

                if (! Schema::hasColumn('restaurant_order_item_selections', 'inventory_unit_factor')) {
                    $table->decimal('inventory_unit_factor', 12, 6)->default(1)->after('inventory_unit');
                }

                if (! Schema::hasColumn('restaurant_order_item_selections', 'stock_quantity')) {
                    $table->decimal('stock_quantity', 12, 6)->default(0)->after('inventory_unit_factor');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('restaurant_order_item_selections')) {
            Schema::table('restaurant_order_item_selections', function (Blueprint $table) {
                foreach (['stock_quantity', 'inventory_unit_factor', 'inventory_unit', 'inventory_quantity'] as $column) {
                    if (Schema::hasColumn('restaurant_order_item_selections', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('product_modifier_options')) {
            Schema::table('product_modifier_options', function (Blueprint $table) {
                foreach (['inventory_unit_factor', 'inventory_unit', 'inventory_quantity'] as $column) {
                    if (Schema::hasColumn('product_modifier_options', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
