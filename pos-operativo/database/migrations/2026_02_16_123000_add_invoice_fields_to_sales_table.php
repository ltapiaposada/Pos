<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->timestamp('invoiced_at')->nullable()->after('sold_at')->index();
            $table->foreignId('invoiced_by_user_id')->nullable()->after('invoiced_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoiced_by_user_id');
            $table->dropColumn('invoiced_at');
        });
    }
};
