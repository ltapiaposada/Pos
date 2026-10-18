<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->uuid('tenant_uuid')->nullable()->after('id')->unique();
        });

        DB::table('companies')->whereNull('tenant_uuid')->orderBy('id')->each(function (object $company): void {
            DB::table('companies')->where('id', $company->id)->update(['tenant_uuid' => (string) Str::uuid()]);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->uuid('external_customer_uuid')->nullable()->after('company_id');
            $table->index(['company_id', 'external_customer_uuid'], 'customers_company_external_customer_index');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->uuid('external_order_uuid')->nullable()->after('company_id')->unique();
        });

        Schema::create('company_entitlements', function (Blueprint $table) {
            $table->id();
            // Referencia lógica a administracion.companies.id; sin FK entre aplicaciones.
            $table->unsignedBigInteger('company_id');
            $table->string('service', 50);
            $table->string('status', 20)->default('enabled');
            $table->timestamp('expires_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'service']);
        });

        Schema::create('integration_outbox_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_uuid')->unique();
            $table->string('event_type', 100);
            $table->json('payload');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_outbox_events');
        Schema::dropIfExists('company_entitlements');

        Schema::table('sales', fn (Blueprint $table) => $table->dropColumn('external_order_uuid'));
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('customers_company_external_customer_index');
            $table->dropColumn('external_customer_uuid');
        });
        Schema::table('companies', fn (Blueprint $table) => $table->dropColumn('tenant_uuid'));
    }
};
