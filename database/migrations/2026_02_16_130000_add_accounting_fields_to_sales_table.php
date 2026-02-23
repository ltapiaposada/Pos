<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->timestamp('accounted_at')->nullable()->after('invoiced_by_user_id')->index();
            $table->foreignId('accounted_by_user_id')->nullable()->after('accounted_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('accounted_by_user_id');
            $table->dropColumn('accounted_at');
        });
    }
};
