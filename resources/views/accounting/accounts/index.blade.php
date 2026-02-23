@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Plan unico de cuentas</h1>
                <p class="page-subtitle">Catalogo PUC para registro contable</p>
            </div>
            <div class="page-actions">
                <form method="GET" class="join">
                    <input name="q" value="{{ request('q') }}" placeholder="Buscar cuenta" class="input input-bordered join-item input-sm">
                    <button class="btn btn-outline btn-sm join-item">Buscar</button>
                </form>
                <a href="{{ route('accounting.accounts.create') }}" class="btn btn-primary btn-sm">Nueva cuenta</a>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Naturaleza</th>
                            <th>Nivel</th>
                            <th>Movible</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accounts as $account)
                            <tr>
                                <td>{{ $account->code }}</td>
                                <td>
                                    <div class="font-medium">{{ $account->name }}</div>
                                    @if ($account->parent)
                                        <div class="text-xs text-base-content/60">Padre: {{ $account->parent->code }}</div>
                                    @endif
                                </td>
                                <td class="capitalize">{{ $account->type }}</td>
                                <td>{{ $account->nature === 'debit' ? 'Debito' : 'Credito' }}</td>
                                <td>{{ $account->level }}</td>
                                <td>{{ $account->is_postable ? 'Si' : 'No' }}</td>
                                <td class="text-right">
                                    <a href="{{ route('accounting.accounts.edit', $account) }}" class="btn btn-outline-primary btn-xs">Editar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-base-content/60">Sin cuentas registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $accounts->links() }}
    </div>
@endsection

