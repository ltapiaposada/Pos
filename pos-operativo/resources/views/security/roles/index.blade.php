@extends('layouts.admin')

@section('content')
    @php
        $roleLabels = config('security_labels.roles', []);
        $permissionLabels = config('security_labels.permissions', []);
    @endphp

    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Roles y permisos</h1>
                <p class="page-subtitle">Define permisos por cada rol</p>
            </div>
        </div>
    </div>

    <div class="mt-6 space-y-4">
        @foreach ($roles as $role)
            <div class="panel">
                <div class="panel-body">
                    <form method="POST" action="{{ route('security.roles.update', $role) }}">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-base font-semibold">Rol: {{ $roleLabels[$role->name] ?? $role->name }}</h2>
                            <button class="btn btn-primary btn-sm">Guardar permisos</button>
                        </div>
                        <div class="grid gap-2 sm:grid-cols-3 lg:grid-cols-4">
                            @foreach ($permissions as $permission)
                                <label class="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $permission->name }}"
                                        class="checkbox checkbox-primary"
                                        @checked($role->permissions->pluck('name')->contains($permission->name))
                                    >
                                    <span>{{ $permissionLabels[$permission->name] ?? $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endsection
