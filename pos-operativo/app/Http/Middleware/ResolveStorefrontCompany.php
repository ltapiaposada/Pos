<?php

namespace App\Http\Middleware;

use App\Support\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResolveStorefrontCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $company = CompanyContext::resolvePublicCompanyFromRequest($request);

        if (! $company) {
            abort(404, 'No existe una tienda publica asociada a este dominio.');
        }

        $request->attributes->set('public_company_id', $company->id);
        $request->attributes->set('public_company', $company);

        $user = $request->user();
        if ($user && $user->hasRole('customer') && $user->company_id !== null && (int) $user->company_id !== (int) $company->id) {
            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->to($request->fullUrl())
                ->with('status', 'Tu sesion pertenecia a otra tienda y se cerro para continuar aqui.');
        }

        return $next($request);
    }
}
