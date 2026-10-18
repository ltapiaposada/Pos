<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_uuid')->unique();
            $table->string('name');
            $table->string('domain')->nullable()->unique();
            $table->string('identification', 100)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('address')->nullable();
            $table->unsignedBigInteger('company_type_id')->nullable();
            $table->string('status', 20)->default('active');
            $table->boolean('kickoff_completed')->default(false);
            $table->string('primary_admin_name')->nullable();
            $table->string('primary_admin_email')->nullable();
            $table->uuid('keycloak_subject')->nullable();
            $table->timestamps();
        });

        Schema::create('platform_services', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_service_id')->constrained('platform_services')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug', 80)->unique();
            $table->string('billing_period', 20);
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->foreignId('platform_service_id')->constrained('platform_services')->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30)->default('active');
            $table->string('payment_status', 30)->default('paid');
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'status', 'ends_at']);
        });

        Schema::create('entitlements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('service', 50);
            $table->string('status', 20)->default('enabled');
            $table->timestamp('expires_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'service']);
        });

        Schema::create('tenant_domains', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('domain')->unique();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });

        Schema::create('provisioning_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('target', 50);
            $table->string('action', 50);
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->json('payload');
            $table->text('last_error')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'target']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provisioning_jobs');
        Schema::dropIfExists('tenant_domains');
        Schema::dropIfExists('entitlements');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('platform_services');
        Schema::dropIfExists('companies');
    }
};
