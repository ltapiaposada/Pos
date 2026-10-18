<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_customers', function (Blueprint $table): void {
            $table->dropUnique('ecommerce_customers_email_unique');
            $table->unique(['company_id', 'email']);
        });

        Schema::create('ecommerce_customer_password_resets', function (Blueprint $table): void {
            $table->unsignedBigInteger('company_id');
            $table->string('email');
            $table->string('token');
            $table->timestamp('created_at')->nullable();
            $table->primary(['company_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_customer_password_resets');

        Schema::table('ecommerce_customers', function (Blueprint $table): void {
            $table->dropUnique('ecommerce_customers_company_id_email_unique');
            $table->unique('email');
        });
    }
};
