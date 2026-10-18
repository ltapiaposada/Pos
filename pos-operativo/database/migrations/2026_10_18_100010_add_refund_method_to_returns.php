<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('returns', function (Blueprint $table): void {
            $table->string('refund_method', 20)->default('cash')->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('returns', function (Blueprint $table): void {
            $table->dropColumn('refund_method');
        });
    }
};
