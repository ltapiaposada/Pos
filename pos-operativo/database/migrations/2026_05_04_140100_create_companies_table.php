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
            $table->string('name');
            $table->string('identification', 100)->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('phone', 50)->nullable();
            $table->string('address')->nullable();
            $table->foreignId('company_type_id')->nullable()->constrained('company_types')->nullOnDelete();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
