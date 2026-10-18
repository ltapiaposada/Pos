<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // El esquema de pruebas conserva empresas locales para no depender de MySQL.
        if (app()->environment('testing') || ! Schema::hasTable('companies')) {
            return;
        }

        if (! Schema::connection('administracion')->hasTable('companies')) {
            throw new RuntimeException('Primero ejecute las migraciones de Administracion: falta la tabla administracion.companies.');
        }

        DB::table('companies')->orderBy('id')->each(function (object $company): void {
            $tenantUuid = $company->tenant_uuid ?: (string) Str::uuid();
            $administracion = DB::connection('administracion')->table('companies');
            $sameTenant = $administracion->where('tenant_uuid', $tenantUuid)->first();
            $sameId = $administracion->where('id', $company->id)->first();

            if (($sameTenant && $sameTenant->id !== $company->id) || ($sameId && $sameId->tenant_uuid !== $tenantUuid)) {
                throw new RuntimeException("La compañía {$company->id} no conserva la misma identidad en Administración. Corrija la duplicidad antes de continuar.");
            }

            $administracion->updateOrInsert(
                ['id' => $company->id],
                [
                    'tenant_uuid' => $tenantUuid,
                    'name' => $company->name,
                    'domain' => $company->domain,
                    'identification' => $company->identification,
                    'email' => $company->email,
                    'phone' => $company->phone,
                    'address' => $company->address,
                    'company_type_id' => $company->company_type_id,
                    'status' => $company->status,
                    'kickoff_completed' => $company->kickoff_completed,
                    'created_at' => $company->created_at,
                    'updated_at' => $company->updated_at,
                ]
            );
        });

        $this->dropCompanyForeignKeys();
        Schema::drop('companies');
    }

    public function down(): void
    {
        // No se recrea una copia local: Administración conserva el maestro de compañías.
    }

    private function dropCompanyForeignKeys(): void
    {
        $foreignKeys = DB::select(
            'SELECT DISTINCT table_name, constraint_name FROM information_schema.key_column_usage WHERE table_schema = DATABASE() AND referenced_table_name = ?',
            ['companies']
        );

        foreach ($foreignKeys as $foreignKey) {
            [$foreignTable, $foreignConstraint] = array_values((array) $foreignKey);
            $table = str_replace('`', '``', (string) $foreignTable);
            $constraint = str_replace('`', '``', (string) $foreignConstraint);
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`");
        }
    }
};
