<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecommerce_customers', function (Blueprint $table): void {
            $table->id();
            // Referencia lógica a administracion.companies.id; no hay FK entre apps.
            $table->unsignedBigInteger('company_id')->index();
            $table->uuid('external_customer_uuid');
            $table->string('name');
            $table->string('email');
            $table->string('phone', 32)->nullable();
            $table->string('address')->nullable();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            // Un correo identifica una cuenta de comprador en la plataforma.
            $table->unique('email');
            $table->unique(['company_id', 'external_customer_uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_customers');
    }
};
