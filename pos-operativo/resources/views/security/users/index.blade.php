@extends('layouts.admin')

@section('content')
    @php
        $roleLabels = config('security_labels.roles', []);
        $sortLink = function (string $column) use ($sort, $dir) {
            $nextDir = $sort === $column && $dir === 'asc' ? 'desc' : 'asc';
            return route('security.users.index', array_merge(request()->query(), [
                'sort' => $column,
                'dir' => $nextDir,
            ]));
        };
        $sortArrow = function (string $column) use ($sort, $dir) {
            if ($sort !== $column) {
                return '';
            }
            return $dir === 'asc' ? ' ▲' : ' ▼';
        };
    @endphp

    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Usuarios</h1>
                <p class="page-subtitle">Gestion de usuarios, roles y permisos</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('security.users.create') }}" class="btn btn-primary btn-sm">Nuevo usuario</a>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <form class="grid gap-3 sm:grid-cols-4 mb-4">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="dir" value="{{ $dir }}">
                <div class="sm:col-span-2">
                    <label class="field-label">Buscar</label>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Nombre o correo"
                        class="input input-bordered w-full"
                    >
                </div>
                <div>
                    <label class="field-label">Sucursal</label>
                    <select name="branch_id" class="select select-bordered w-full">
                        <option value="">Todas</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">Rol</label>
                    <select name="role" class="select select-bordered w-full">
                        <option value="">Todos</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $roleLabels[$role->name] ?? $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-4 flex gap-2">
                    <button class="btn btn-primary btn-sm">Filtrar</button>
                    <a href="{{ route('security.users.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
                </div>
            </form>

            <div class="space-y-3 md:hidden">
                @forelse ($users as $user)
                    <article class="surface-muted rounded-2xl p-4">
                        <p class="text-sm font-semibold">{{ $user->name }}</p>
                        <p class="text-xs text-base-content/60">{{ $user->email }}</p>
                        <div class="mt-2 space-y-1 text-sm">
                            <p><span class="text-base-content/60">Sucursal:</span> {{ $user->branch?->name ?? '-' }}</p>
                            <p>
                                <span class="text-base-content/60">Roles:</span>
                                {{
                                    $user->roles
                                        ->pluck('name')
                                        ->map(fn ($name) => $roleLabels[$name] ?? $name)
                                        ->implode(', ') ?: '-'
                                }}
                            </p>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('security.users.edit', $user) }}" class="btn btn-outline btn-xs">Editar</a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-base-300 bg-base-100 p-5 text-center text-sm text-base-content/60">Sin usuarios registrados.</div>
                @endforelse
            </div>

            <div class="overflow-x-auto hidden md:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th><a href="{{ $sortLink('name') }}" class="link link-hover">Nombre{!! $sortArrow('name') !!}</a></th>
                            <th><a href="{{ $sortLink('email') }}" class="link link-hover">Correo{!! $sortArrow('email') !!}</a></th>
                            <th><a href="{{ $sortLink('branch') }}" class="link link-hover">Sucursal{!! $sortArrow('branch') !!}</a></th>
                            <th><a href="{{ $sortLink('role') }}" class="link link-hover">Roles{!! $sortArrow('role') !!}</a></th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->branch?->name ?? '-' }}</td>
                                <td>
                                    {{
                                        $user->roles
                                            ->pluck('name')
                                            ->map(fn ($name) => $roleLabels[$name] ?? $name)
                                            ->implode(', ') ?: '-'
                                    }}
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('security.users.edit', $user) }}" class="btn btn-outline btn-xs">Editar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-base-content/60">Sin usuarios registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endsection
