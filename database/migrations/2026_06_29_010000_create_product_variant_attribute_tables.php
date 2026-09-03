<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variant_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });

        Schema::create('product_variant_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_attribute_id');
            $table->string('value', 120);
            $table->timestamps();

            $table->foreign('product_variant_attribute_id', 'variant_attr_values_attr_fk')
                ->references('id')
                ->on('product_variant_attributes')
                ->cascadeOnDelete();
            $table->unique(['product_variant_attribute_id', 'value'], 'variant_attribute_values_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_attribute_values');
        Schema::dropIfExists('product_variant_attributes');
    }
};
