<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('plan_type', 50);
            $table->string('billing_period', 20);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 30)->default('active')->index();
            $table->string('payment_status', 30)->default('paid')->index();
            $table->date('last_payment_date')->nullable();
            $table->date('next_payment_date')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_subscriptions');
    }
};
