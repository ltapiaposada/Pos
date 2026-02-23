<?php

namespace App\Http\Controllers;

use App\Http\Requests\CashMovementRequest;
use App\Http\Requests\CashRegisterCloseRequest;
use App\Http\Requests\CashRegisterOpenRequest;
use App\Models\Branch;
use App\Models\CashRegisterSession;
use App\Services\CashRegisterService;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::query()->orderBy('name')->get();
        $branchId = $request->get('branch_id', $request->user()->branch_id ?? $branches->first()?->id);

        $session = CashRegisterSession::query()
            ->with('branch')
            ->where('branch_id', $branchId)
            ->where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->first();

        return view('cash-register.index', compact('branches', 'branchId', 'session'));
    }

    public function open(CashRegisterOpenRequest $request, CashRegisterService $service)
    {
        $service->open(
            $request->integer('branch_id'),
            $request->user()->id,
            (float) $request->input('opening_amount')
        );

        $redirectTo = (string) $request->input('redirect_to', '');
        if ($redirectTo !== '' && str_starts_with($redirectTo, '/')) {
            return redirect($redirectTo)->with('status', 'Caja abierta.');
        }

        return redirect()->route('cash-register.index')->with('status', 'Caja abierta.');
    }

    public function close(CashRegisterCloseRequest $request, CashRegisterService $service)
    {
        $branchId = $request->integer('branch_id');
        $session = CashRegisterSession::query()
            ->where('branch_id', $branchId)
            ->where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->first();

        if (! $session) {
            return redirect()
                ->route('cash-register.index', ['branch_id' => $branchId])
                ->withErrors(['cash_register' => 'No hay una caja abierta para cerrar en la sucursal seleccionada.']);
        }

        $service->close($session, (float) $request->input('closing_amount'));

        return redirect()->route('cash-register.index', ['branch_id' => $branchId])->with('status', 'Caja cerrada.');
    }

    public function movement(CashMovementRequest $request, CashRegisterService $service)
    {
        $branchId = $request->integer('branch_id');
        $session = CashRegisterSession::query()
            ->where('branch_id', $branchId)
            ->where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->first();

        if (! $session) {
            return redirect()
                ->route('cash-register.index', ['branch_id' => $branchId])
                ->withErrors(['cash_register' => 'No hay una caja abierta para registrar movimiento en la sucursal seleccionada.']);
        }

        $service->recordMovement($session, $request->user()->id, $request->validated());

        return redirect()->route('cash-register.index', ['branch_id' => $branchId])->with('status', 'Movimiento registrado.');
    }
}
