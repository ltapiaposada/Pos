<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['IN', 'OUT']);
            $table->decimal('quantity', 12, 3);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->string('ref_type', 64)->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'product_id']);
            $table->index(['ref_type', 'ref_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
