<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ImplementationProgressController extends Controller
{
    public function index(): View
    {
        $steps = [
            ['label' => 'Analizar estructura actual', 'completed' => true],
            ['label' => 'Identificar tablas existentes', 'completed' => true],
            ['label' => 'Reutilizar tablas actuales cuando aplique', 'completed' => true],
            ['label' => 'Crear o ajustar empresas', 'completed' => true],
            ['label' => 'Crear o ajustar tipos de empresa', 'completed' => true],
            ['label' => 'Crear o ajustar suscripciones', 'completed' => true],
            ['label' => 'Relacionar usuarios con empresas', 'completed' => true],
            ['label' => 'Relacionar tablas operativas con empresa', 'completed' => true],
            ['label' => 'Ajustar autenticación', 'completed' => true],
            ['label' => 'Aplicar filtros por empresa', 'completed' => true],
            ['label' => 'Crear panel global', 'completed' => true],
            ['label' => 'Crear gestión de suscripciones', 'completed' => true],
            ['label' => 'Mostrar aviso 4 días antes', 'completed' => true],
            ['label' => 'Bloquear sistema vencido', 'completed' => true],
            ['label' => 'Probar aislamiento de datos', 'completed' => true],
        ];

        $findings = [
            'Ya existe branches y users.branch_id, por lo que la app ya separa parte de la operación por sucursal.',
            'Las tablas operativas más sensibles ya usan branch_id: ventas, compras, inventario, caja y devoluciones.',
            'La base ya incluye companies, company_types y company_subscriptions con migración de datos iniciales.',
            'Las tablas maestras principales quedaron ligadas a company_id y ahora usan scopes por empresa.',
            'La autenticación sigue sobre Breeze y Spatie Permission, pero ya reconoce company_id y el rol global system_owner.',
            'El registro público ya asigna empresa y sucursal por defecto para no dejar usuarios huérfanos.',
            'Las tablas operativas principales y sus tablas hijas ya guardan company_id explícito además de branch_id.',
        ];

        return view('admin.implementation-progress', compact('steps', 'findings'));
    }
}
