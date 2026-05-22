<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'inventories',
            'inventory_movements',
            'cash_register_sessions',
            'sales',
            'sale_items',
            'payments',
            'cash_movements',
            'returns',
            'return_items',
            'purchases',
            'purchase_items',
            'purchase_payments',
        ] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
                $table->index('company_id');
            });
        }

        $this->backfillFromBranch('inventories');
        $this->backfillFromBranch('inventory_movements');
        $this->backfillFromBranch('cash_register_sessions');
        $this->backfillFromBranch('sales');
        $this->backfillFromBranch('cash_movements');
        $this->backfillFromBranch('returns');
        $this->backfillFromBranch('purchases');

        $this->backfillFromParent('sale_items', 'sale_id', 'sales');
        $this->backfillFromParent('payments', 'sale_id', 'sales');
        $this->backfillFromParent('return_items', 'return_id', 'returns');
        $this->backfillFromParent('purchase_items', 'purchase_id', 'purchases');
        $this->backfillFromParent('purchase_payments', 'purchase_id', 'purchases');

        Schema::table('inventories', function (Blueprint $table) {
            $table->index(['company_id', 'branch_id', 'product_id']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->index(['company_id', 'branch_id', 'sold_at']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->index(['company_id', 'branch_id', 'purchased_at']);
        });

        Schema::table('returns', function (Blueprint $table) {
            $table->index(['company_id', 'branch_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('returns', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'branch_id', 'created_at']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'branch_id', 'purchased_at']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'branch_id', 'sold_at']);
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'branch_id', 'product_id']);
        });

        foreach ([
            'purchase_payments',
            'purchase_items',
            'purchases',
            'return_items',
            'returns',
            'cash_movements',
            'payments',
            'sale_items',
            'sales',
            'cash_register_sessions',
            'inventory_movements',
            'inventories',
        ] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('company_id');
            });
        }
    }

    private function backfillFromBranch(string $tableName): void
    {
        $rows = DB::table($tableName)
            ->whereNull('company_id')
            ->select(['id', 'branch_id'])
            ->get();

        foreach ($rows as $row) {
            $companyId = DB::table('branches')->where('id', $row->branch_id)->value('company_id');

            DB::table($tableName)
                ->where('id', $row->id)
                ->update(['company_id' => $companyId]);
        }
    }

    private function backfillFromParent(string $tableName, string $foreignKey, string $parentTable): void
    {
        $rows = DB::table($tableName)
            ->whereNull('company_id')
            ->select(['id', $foreignKey])
            ->get();

        foreach ($rows as $row) {
            $companyId = DB::table($parentTable)->where('id', $row->{$foreignKey})->value('company_id');

            DB::table($tableName)
                ->where('id', $row->id)
                ->update(['company_id' => $companyId]);
        }
    }
};
