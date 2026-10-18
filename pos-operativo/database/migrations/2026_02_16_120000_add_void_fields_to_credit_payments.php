<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->timestamp('voided_at')->nullable()->after('paid_at');
            $table->foreignId('voided_by_user_id')->nullable()->after('voided_at')->constrained('users')->nullOnDelete();
            $table->string('void_reason')->nullable()->after('voided_by_user_id');
            $table->index(['sale_id', 'voided_at']);
        });

        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->timestamp('voided_at')->nullable()->after('paid_at');
            $table->foreignId('voided_by_user_id')->nullable()->after('voided_at')->constrained('users')->nullOnDelete();
            $table->string('void_reason')->nullable()->after('voided_by_user_id');
            $table->index(['purchase_id', 'voided_at']);
        });
    }

    public function down(): void
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->dropIndex('purchase_payments_purchase_id_voided_at_index');
            $table->dropConstrainedForeignId('voided_by_user_id');
            $table->dropColumn(['voided_at', 'void_reason']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_sale_id_voided_at_index');
            $table->dropConstrainedForeignId('voided_by_user_id');
            $table->dropColumn(['voided_at', 'void_reason']);
        });
    }
};
