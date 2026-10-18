<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Los contactos y sus contraseñas pertenecen al POS. Esta tabla fue una
        // duplicación temporal y no debe existir en la base de e-commerce.
        Schema::dropIfExists('ecommerce_customers');

        Schema::table('ecommerce_customer_password_resets', function (Blueprint $table): void {
            $table->uuid('external_customer_uuid')->nullable()->after('company_id');
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_customer_password_resets', fn (Blueprint $table) => $table->dropColumn('external_customer_uuid'));
    }
};
