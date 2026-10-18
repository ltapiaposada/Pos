<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_modifier_groups')) {
            Schema::create('product_modifier_groups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('selection_type', 20)->default('single');
                $table->boolean('is_required')->default(false);
                $table->unsignedInteger('min_select')->default(0);
                $table->unsignedInteger('max_select')->default(1);
                $table->unsignedInteger('display_order')->default(0);
                $table->timestamps();

                $table->index(['company_id', 'product_id'], 'pmg_company_product_idx');
            });
        }

        if (! Schema::hasTable('product_modifier_options')) {
            Schema::create('product_modifier_options', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('product_modifier_group_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                $table->string('label');
                $table->decimal('price_delta', 12, 2)->default(0);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('display_order')->default(0);
                $table->timestamps();

                $table->index(['company_id', 'product_modifier_group_id'], 'pmo_company_group_idx');
            });
        }

        if (! Schema::hasTable('restaurant_order_item_selections')) {
            Schema::create('restaurant_order_item_selections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedBigInteger('restaurant_order_item_id');
                $table->unsignedBigInteger('product_modifier_group_id')->nullable();
                $table->unsignedBigInteger('product_modifier_option_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('group_name');
                $table->string('option_label');
                $table->string('selection_action', 20)->default('include');
                $table->decimal('price_delta', 12, 2)->default(0);
                $table->timestamps();

                $table->foreign('restaurant_order_item_id', 'rois_item_fk')
                    ->references('id')
                    ->on('restaurant_order_items')
                    ->cascadeOnDelete();
                $table->foreign('product_modifier_group_id', 'rois_group_fk')
                    ->references('id')
                    ->on('product_modifier_groups')
                    ->nullOnDelete();
                $table->foreign('product_modifier_option_id', 'rois_option_fk')
                    ->references('id')
                    ->on('product_modifier_options')
                    ->nullOnDelete();
                $table->foreign('product_id', 'rois_product_fk')
                    ->references('id')
                    ->on('products')
                    ->nullOnDelete();
                $table->index(['company_id', 'restaurant_order_item_id'], 'rois_company_item_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_order_item_selections');
        Schema::dropIfExists('product_modifier_options');
        Schema::dropIfExists('product_modifier_groups');
    }
};
