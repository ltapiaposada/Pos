<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cash_register_session_id')->nullable()->constrained('cash_register_sessions')->nullOnDelete();
            $table->unsignedBigInteger('sale_number');
            $table->string('status', 20)->default('paid');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_total', 12, 2)->default(0);
            $table->decimal('change_total', 12, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->timestamp('sold_at');
            $table->timestamps();

            $table->unique(['branch_id', 'sale_number']);
            $table->index(['branch_id', 'sold_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
