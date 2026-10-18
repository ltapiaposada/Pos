<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            // El contacto del POS es la única cuenta de comprador. No se replica
            // en e-commerce: solo e-commerce verifica sus credenciales por API.
            $table->string('password')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('customers', fn (Blueprint $table) => $table->dropColumn('password'));
    }
};
