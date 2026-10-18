<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecommerce_storefronts', function (Blueprint $table) {
            $table->id();
            // Referencia lógica a administracion.companies.id. Sin FK entre aplicaciones.
            $table->unsignedBigInteger('company_id')->index();
            $table->string('domain')->unique();
            $table->string('status', 20)->default('enabled');
            $table->decimal('flat_shipping', 12, 2)->default(0);
            $table->json('coupons')->nullable();
            $table->timestamps();
        });

        Schema::create('ecommerce_catalog_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('pos_product_id');
            $table->string('name');
            $table->string('sku', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('currency', 10);
            $table->decimal('tax_rate', 8, 2)->default(0);
            $table->decimal('stock', 14, 3)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('payload')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'pos_product_id']);
        });

        Schema::create('ecommerce_customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->uuid('keycloak_subject');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'keycloak_subject']);
        });

        Schema::create('ecommerce_checkout_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->uuid('external_order_uuid')->unique();
            $table->uuid('external_customer_uuid');
            $table->unsignedBigInteger('pos_sale_id')->nullable();
            $table->string('status', 30)->default('pending_pos');
            $table->decimal('total', 12, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->json('request_payload');
            $table->json('pos_response')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'status']);
        });

        Schema::create('ecommerce_inbound_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_uuid')->unique();
            $table->string('event_type', 100);
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_inbound_events');
        Schema::dropIfExists('ecommerce_checkout_orders');
        Schema::dropIfExists('ecommerce_customer_profiles');
        Schema::dropIfExists('ecommerce_catalog_products');
        Schema::dropIfExists('ecommerce_storefronts');
    }
};
