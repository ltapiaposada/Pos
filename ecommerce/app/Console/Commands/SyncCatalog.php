<?php

namespace App\Console\Commands;

use App\Models\AdministrativeTenant;
use App\Services\CatalogSynchronizer;
use Illuminate\Console\Command;

class SyncCatalog extends Command
{
    protected $signature = 'catalog:sync {tenantUuid? : UUID del tenant} {--all : Sincroniza todos los tenants activos}';

    protected $description = 'Sincroniza la proyeccion de catalogo y disponibilidad desde POS.';

    public function handle(CatalogSynchronizer $synchronizer): int
    {
        $tenants = $this->option('all')
            ? AdministrativeTenant::where('status', 'active')->get()
            : AdministrativeTenant::where('tenant_uuid', $this->argument('tenantUuid'))->get();

        if ($tenants->isEmpty()) {
            $this->error('No se encontro un tenant para sincronizar.');
            return self::FAILURE;
        }

        foreach ($tenants as $tenant) {
            $synchronizer->sync($tenant);
            $this->info("Catalogo sincronizado: {$tenant->name}");
        }

        return self::SUCCESS;
    }
}
