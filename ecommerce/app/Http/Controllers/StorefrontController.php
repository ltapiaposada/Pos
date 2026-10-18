<?php

namespace App\Http\Controllers;

use App\Models\AdministrativeTenant;
use App\Models\CatalogProduct;
use App\Models\Storefront;
use App\Services\CatalogSynchronizer;
use App\Services\StorefrontCart;
use App\Services\StorefrontContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(Request $request): View
    {
        return $this->renderStorefront(app(StorefrontContext::class)->fromRequest($request), $request->boolean('sync'));
    }

    public function preview(Request $request, int $companyId): View
    {
        return $this->renderStorefront(app(StorefrontContext::class)->preview($companyId), $request->boolean('sync'));
    }

    /** @param array{company: AdministrativeTenant, storefront: Storefront, preview: bool} $context */
    private function renderStorefront(array $context, bool $sync): View
    {
        $company = $context['company'];
        $storefront = $context['storefront'];
        $lastSync = CatalogProduct::query()
            ->where('company_id', $company->id)
            ->max('synced_at');

        // Sin workers: la primera visita carga el catálogo y después se
        // refresca como máximo una vez cada cinco minutos por tienda.
        if ($sync || ! $lastSync || now()->diffInMinutes($lastSync) >= 5) {
            try {
                app(CatalogSynchronizer::class)->sync($company);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return view('storefront.index', [
            'company' => $company,
            'storefront' => $storefront,
            'products' => CatalogProduct::query()->where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get(),
            'preview' => $context['preview'],
            'cartCount' => app(StorefrontCart::class)->count($company->id),
        ]);
    }
}
