<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('optometry_patient_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('occupation')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 32)->nullable();
            $table->text('allergies')->nullable();
            $table->text('systemic_history')->nullable();
            $table->text('ocular_history')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'customer_id']);
        });

        Schema::create('clinical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('examined_at');
            $table->text('reason_for_consultation');
            $table->text('medical_history')->nullable();
            $table->text('ocular_history')->nullable();
            $table->text('examination')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('observations')->nullable();
            $table->string('professional_name')->nullable();
            $table->string('professional_license')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'customer_id', 'examined_at']);
        });

        Schema::create('medical_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clinical_record_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('ordered_at');
            $table->string('status', 20)->default('draft');
            $table->text('prescription_details');
            $table->text('usage_instructions')->nullable();
            $table->text('observations')->nullable();
            $table->string('professional_name')->nullable();
            $table->string('professional_license')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'customer_id', 'status']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('medical_order_id')->nullable()->after('customer_id')->constrained('medical_orders')->nullOnDelete();
            $table->unique('medical_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique(['medical_order_id']);
            $table->dropConstrainedForeignId('medical_order_id');
        });

        Schema::dropIfExists('medical_orders');
        Schema::dropIfExists('clinical_records');
        Schema::dropIfExists('optometry_patient_profiles');
    }
};
