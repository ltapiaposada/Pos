<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('delivery_instructions')->nullable()->after('description');
        });
        Schema::table('sale_items', function (Blueprint $table) {
            $table->text('delivery_instructions')->nullable()->after('barcode');
        });

        Schema::create('inventory_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('lot_number', 100);
            $table->date('expires_at')->nullable();
            $table->decimal('quantity', 12, 3);
            $table->decimal('remaining_quantity', 12, 3);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'branch_id', 'product_id', 'lot_number']);
            $table->index(['branch_id', 'product_id', 'expires_at']);
        });

        Schema::create('inventory_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sale_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('serial_number', 150);
            $table->string('status', 20)->default('available');
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'product_id', 'serial_number']);
            $table->index(['branch_id', 'product_id', 'status']);
        });

        Schema::create('sale_item_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_lot_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->decimal('returned_quantity', 12, 3)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_item_lots');
        Schema::dropIfExists('inventory_serials');
        Schema::dropIfExists('inventory_lots');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('delivery_instructions');
        });
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('delivery_instructions');
        });
    }
};
