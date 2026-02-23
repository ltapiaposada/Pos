<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_type', 16)->default('simple')->after('unit');
            $table->foreignId('parent_product_id')->nullable()->after('product_type')->constrained('products')->nullOnDelete();
        });

        Schema::create('product_kit_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kit_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('component_product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->timestamps();

            $table->unique(['kit_product_id', 'component_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_kit_items');

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_product_id');
            $table->dropColumn('product_type');
        });
    }
};

