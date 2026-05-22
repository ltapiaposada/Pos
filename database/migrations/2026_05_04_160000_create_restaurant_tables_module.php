<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('restaurant_tables')) {
            Schema::create('restaurant_tables', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('number', 50);
                $table->unsignedInteger('capacity')->default(1);
                $table->string('status', 30)->default('available');
                $table->string('location')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['branch_id', 'number']);
                $table->index(['company_id', 'branch_id', 'status']);
            });
        }

        if (! Schema::hasTable('restaurant_orders')) {
            Schema::create('restaurant_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('restaurant_table_id')->nullable()->constrained('restaurant_tables')->nullOnDelete();
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedBigInteger('order_number');
                $table->string('order_type', 30)->default('dine_in');
                $table->string('status', 30)->default('open');
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('tax', 12, 2)->default(0);
                $table->decimal('discount', 12, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamp('opened_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();

                $table->unique(['branch_id', 'order_number']);
                $table->index(['company_id', 'branch_id', 'status']);
                $table->index(['company_id', 'sale_id']);
            });
        }

        if (! Schema::hasTable('restaurant_order_items')) {
            Schema::create('restaurant_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('restaurant_order_id')->constrained('restaurant_orders')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->restrictOnDelete();
                $table->decimal('quantity', 12, 3);
                $table->decimal('unit_price', 12, 2);
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->text('notes')->nullable();
                $table->string('kitchen_status', 30)->default('pending');
                $table->timestamps();

                $table->index(['company_id', 'restaurant_order_id']);
                $table->index(['company_id', 'kitchen_status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_order_items');
        Schema::dropIfExists('restaurant_orders');
        Schema::dropIfExists('restaurant_tables');
    }
};
