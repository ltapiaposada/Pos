<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_kit_items', function (Blueprint $table) {
            $table->string('component_unit', 32)->nullable()->after('quantity');
            $table->decimal('component_unit_factor', 12, 6)->default(1)->after('component_unit');
        });
    }

    public function down(): void
    {
        Schema::table('product_kit_items', function (Blueprint $table) {
            $table->dropColumn(['component_unit', 'component_unit_factor']);
        });
    }
};
