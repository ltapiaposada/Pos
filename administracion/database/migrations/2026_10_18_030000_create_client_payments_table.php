<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_payments', function (Blueprint $table): void {
            $table->id();
            // Referencias lógicas: la compañía y la suscripción son administradas aquí.
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('subscription_id')->nullable()->index();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('COP');
            $table->string('status', 20)->default('pending')->index();
            $table->date('due_date')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->string('method', 50)->nullable();
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_payments');
    }
};
