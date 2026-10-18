<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('company_id');
        });

        foreach (['categories', 'taxes', 'customers', 'products', 'settings', 'accounting_accounts', 'journal_entries'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
                $table->index('company_id');
            });
        }

        $this->seedInitialCompanyContext();
        $this->updateUniqueIndexes();
    }

    public function down(): void
    {
        $this->restoreUniqueIndexes();

        foreach (['journal_entries', 'accounting_accounts', 'settings', 'products', 'customers', 'taxes', 'categories'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('company_id');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
    }

    private function seedInitialCompanyContext(): void
    {
        $timestamp = now();

        DB::table('company_types')->updateOrInsert(
            ['slug' => 'restaurant'],
            [
                'name' => 'Restaurante',
                'features' => json_encode(['tables', 'orders', 'kitchen', 'menu']),
                'is_active' => true,
                'updated_at' => $timestamp,
                'created_at' => $timestamp,
            ]
        );

        DB::table('company_types')->updateOrInsert(
            ['slug' => 'optic'],
            [
                'name' => 'Óptica',
                'features' => json_encode(['optical_prescriptions', 'lenses', 'frames', 'patients']),
                'is_active' => true,
                'updated_at' => $timestamp,
                'created_at' => $timestamp,
            ]
        );

        DB::table('company_types')->updateOrInsert(
            ['slug' => 'pos'],
            [
                'name' => 'POS normal',
                'features' => json_encode(['sales', 'products', 'inventory']),
                'is_active' => true,
                'updated_at' => $timestamp,
                'created_at' => $timestamp,
            ]
        );

        $defaultTypeId = DB::table('company_types')->where('slug', 'pos')->value('id');
        $businessName = DB::table('settings')->where('key', 'business')->value('value');
        $decodedBusinessName = null;

        if (is_string($businessName)) {
            $decoded = json_decode($businessName, true);
            $decodedBusinessName = is_array($decoded) ? ($decoded['name'] ?? null) : null;
        }

        $companyName = $decodedBusinessName ?: 'Empresa principal';

        $existingCompanyId = DB::table('companies')->orderBy('id')->value('id');

        if (! $existingCompanyId) {
            $existingCompanyId = DB::table('companies')->insertGetId([
                'name' => $companyName,
                'identification' => null,
                'email' => null,
                'phone' => null,
                'address' => null,
                'company_type_id' => $defaultTypeId,
                'status' => 'active',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }

        DB::table('branches')->whereNull('company_id')->update(['company_id' => $existingCompanyId]);
        DB::table('users')
            ->whereNull('company_id')
            ->update([
                'company_id' => DB::raw('(select company_id from branches where branches.id = users.branch_id limit 1)'),
            ]);

        foreach (['categories', 'taxes', 'customers', 'products', 'settings', 'accounting_accounts', 'journal_entries'] as $tableName) {
            DB::table($tableName)->whereNull('company_id')->update(['company_id' => $existingCompanyId]);
        }

        $hasSubscription = DB::table('company_subscriptions')
            ->where('company_id', $existingCompanyId)
            ->exists();

        if (! $hasSubscription) {
            DB::table('company_subscriptions')->insert([
                'company_id' => $existingCompanyId,
                'plan_type' => 'pos',
                'billing_period' => 'yearly',
                'start_date' => $timestamp->toDateString(),
                'end_date' => $timestamp->copy()->addYear()->toDateString(),
                'status' => 'active',
                'payment_status' => 'paid',
                'last_payment_date' => $timestamp->toDateString(),
                'next_payment_date' => $timestamp->copy()->addYear()->toDateString(),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }
    }

    private function updateUniqueIndexes(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropUnique('branches_code_unique');
            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'name']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_sku_unique');
            $table->dropUnique('products_barcode_unique');
            $table->unique(['company_id', 'sku']);
            $table->unique(['company_id', 'barcode']);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique('settings_key_unique');
            $table->unique(['company_id', 'key']);
        });

        Schema::table('accounting_accounts', function (Blueprint $table) {
            $table->dropUnique('accounting_accounts_code_unique');
            $table->unique(['company_id', 'code']);
        });
    }

    private function restoreUniqueIndexes(): void
    {
        Schema::table('accounting_accounts', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'code']);
            $table->unique('code');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'key']);
            $table->unique('key');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'sku']);
            $table->dropUnique(['company_id', 'barcode']);
            $table->unique('sku');
            $table->unique('barcode');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'code']);
            $table->dropIndex(['company_id', 'name']);
            $table->unique('code');
        });
    }
};
