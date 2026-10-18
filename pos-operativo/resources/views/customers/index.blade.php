@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Contactos</h1>
                <p class="page-subtitle">Gestiona tu base de contactos para ventas y compras</p>
            </div>
            <div class="page-actions">
                <form method="GET" class="join">
                    <select name="type" class="select select-bordered join-item select-sm">
                        <option value="">Todos</option>
                        <option value="person" @selected(request('type') === 'person')>Persona</option>
                        <option value="company" @selected(request('type') === 'company')>Empresa</option>
                        <option value="supplier" @selected(request('type') === 'supplier')>Proveedor</option>
                    </select>
                    <input name="q" value="{{ request('q') }}" placeholder="Buscar" class="input input-bordered join-item input-sm">
                    <button class="btn btn-outline btn-sm join-item">Buscar</button>
                </form>
                <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm w-full sm:w-auto">Nuevo</a>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <div class="space-y-3 md:hidden">
                @forelse ($customers as $customer)
                    @php
                        $typeLabel = $customer->contact_type === 'person'
                            ? 'Persona'
                            : ($customer->contact_type === 'company' ? 'Empresa' : 'Proveedor');
                    @endphp
                    <article class="surface-muted rounded-2xl p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-sm font-semibold">{{ $customer->name }}</p>
                                <p class="text-xs text-base-content/60">{{ $customer->document ?? '-' }}</p>
                            </div>
                            <span class="badge badge-info badge-sm">{{ $typeLabel }}</span>
                        </div>
                        <div class="mt-2 text-sm">
                            <p><span class="text-base-content/60">Email:</span> {{ $customer->email ?? '-' }}</p>
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="badge {{ $customer->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $customer->is_active ? 'Si' : 'No' }}
                            </span>
                            <div class="actions">
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-primary btn-xs">Editar</a>
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-xs" data-confirm="Eliminar contacto?">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-base-300 bg-base-100 p-5 text-center text-sm text-base-content/60">Sin registros</div>
                @endforelse
            </div>

            <div class="overflow-x-auto hidden md:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Documento</th>
                            <th>Tipo</th>
                            <th>Email</th>
                            <th>Activo</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->document ?? '-' }}</td>
                                <td>
                                    @php
                                        $typeLabel = $customer->contact_type === 'person'
                                            ? 'Persona'
                                            : ($customer->contact_type === 'company' ? 'Empresa' : 'Proveedor');
                                    @endphp
                                    <span class="badge badge-info">{{ $typeLabel }}</span>
                                </td>
                                <td>{{ $customer->email ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $customer->is_active ? 'badge-success' : 'badge-danger' }}">
                                        {{ $customer->is_active ? 'Si' : 'No' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="actions">
                                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-primary btn-xs">Editar</a>
                                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-xs" data-confirm="Eliminar contacto?">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-base-content/60">Sin registros</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $customers->links() }}
    </div>
@endsection
