<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_register_sessions', function (Blueprint $table): void {
            $table->unsignedTinyInteger('open_marker')->nullable()->after('status');
            $table->string('difference_reason', 500)->nullable()->after('difference');
            // NULL permite conservar cualquier cantidad de cierres. El valor 1 solo
            // puede existir una vez por sucursal y cajero mientras la caja está abierta.
            $table->unique(['branch_id', 'user_id', 'open_marker'], 'cash_sessions_one_open_per_user');
        });

        DB::table('cash_register_sessions')
            ->where('status', 'open')
            ->update(['open_marker' => 1]);

        Schema::table('cash_movements', function (Blueprint $table): void {
            $table->string('category', 50)->nullable()->after('type');
            $table->string('source_type', 100)->nullable()->after('reason');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->index(['cash_register_session_id', 'category'], 'cash_movements_session_category_index');
            $table->index(['source_type', 'source_id'], 'cash_movements_source_index');
        });

        DB::table('cash_movements')
            ->whereNull('category')
            ->update(['category' => 'legacy']);
    }

    public function down(): void
    {
        Schema::table('cash_movements', function (Blueprint $table): void {
            $table->dropIndex('cash_movements_session_category_index');
            $table->dropIndex('cash_movements_source_index');
            $table->dropColumn(['category', 'source_type', 'source_id']);
        });

        Schema::table('cash_register_sessions', function (Blueprint $table): void {
            $table->dropUnique('cash_sessions_one_open_per_user');
            $table->dropColumn(['open_marker', 'difference_reason']);
        });
    }
};
