<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('accounting_accounts')) {
            return;
        }

        Schema::table('accounting_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('accounting_accounts', 'parent_account_id')) {
                $table->foreignId('parent_account_id')->nullable()->after('nature')->constrained('accounting_accounts')->nullOnDelete();
            }
            if (! Schema::hasColumn('accounting_accounts', 'level')) {
                $table->unsignedTinyInteger('level')->default(1)->after('parent_account_id');
            }
            if (! Schema::hasColumn('accounting_accounts', 'is_postable')) {
                $table->boolean('is_postable')->default(true)->after('level');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('accounting_accounts')) {
            return;
        }

        Schema::table('accounting_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounting_accounts', 'parent_account_id')) {
                $table->dropConstrainedForeignId('parent_account_id');
            }
            if (Schema::hasColumn('accounting_accounts', 'level')) {
                $table->dropColumn('level');
            }
            if (Schema::hasColumn('accounting_accounts', 'is_postable')) {
                $table->dropColumn('is_postable');
            }
        });
    }
};

