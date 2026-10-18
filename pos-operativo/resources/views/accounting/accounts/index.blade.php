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
            <div class="space-y-3 md:hidden">
                @forelse ($accounts as $account)
                    <article class="surface-muted rounded-2xl p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-sm font-semibold">{{ $account->code }} - {{ $account->name }}</p>
                                @if ($account->parent)
                                    <p class="text-xs text-base-content/60">Padre: {{ $account->parent->code }}</p>
                                @endif
                            </div>
                            <a href="{{ route('accounting.accounts.edit', $account) }}" class="btn btn-outline-primary btn-xs">Editar</a>
                        </div>
                        <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                            <div class="rounded-xl border border-base-300 bg-base-100 px-3 py-2">
                                <p class="text-base-content/60">Tipo</p>
                                <p class="capitalize">{{ $account->type }}</p>
                            </div>
                            <div class="rounded-xl border border-base-300 bg-base-100 px-3 py-2">
                                <p class="text-base-content/60">Naturaleza</p>
                                <p>{{ $account->nature === 'debit' ? 'Debito' : 'Credito' }}</p>
                            </div>
                            <div class="rounded-xl border border-base-300 bg-base-100 px-3 py-2">
                                <p class="text-base-content/60">Nivel</p>
                                <p>{{ $account->level }}</p>
                            </div>
                            <div class="rounded-xl border border-base-300 bg-base-100 px-3 py-2">
                                <p class="text-base-content/60">Movible</p>
                                <p>{{ $account->is_postable ? 'Si' : 'No' }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-base-300 bg-base-100 p-5 text-center text-sm text-base-content/60">Sin cuentas registradas.</div>
                @endforelse
            </div>

            <div class="overflow-x-auto hidden md:block">
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
