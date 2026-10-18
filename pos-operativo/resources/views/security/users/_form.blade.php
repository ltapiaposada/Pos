@csrf
@php
    $roleLabels = config('security_labels.roles', []);
    $permissionLabels = config('security_labels.permissions', []);
@endphp

<div class="form-grid">
    <div>
        <label class="field-label">Nombre</label>
        <input name="name" value="{{ old('name', $user->name ?? '') }}" class="input input-bordered w-full" required>
        @error('name')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="field-label">Correo</label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="input input-bordered w-full" required>
        @error('email')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="field-label">Sucursal</label>
        <select name="branch_id" class="select select-bordered w-full">
            <option value="">Sin sucursal</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) old('branch_id', $user->branch_id ?? '') === (string) $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
        @error('branch_id')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="field-label">{{ isset($user) && $user->exists ? 'Nueva clave (opcional)' : 'Clave' }}</label>
        <input type="password" name="password" class="input input-bordered w-full" {{ isset($user) && $user->exists ? '' : 'required' }}>
        @error('password')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="field-label">Confirmar clave</label>
        <input type="password" name="password_confirmation" class="input input-bordered w-full" {{ isset($user) && $user->exists ? '' : 'required' }}>
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Roles</label>
        <div class="grid gap-2 sm:grid-cols-3">
            @foreach ($roles as $role)
                <label class="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        name="roles[]"
                        value="{{ $role->name }}"
                        class="checkbox checkbox-primary"
                        @checked(collect(old('roles', $user->roles->pluck('name')->all() ?? []))->contains($role->name))
                    >
                    <span>{{ $roleLabels[$role->name] ?? $role->name }}</span>
                </label>
            @endforeach
        </div>
        @error('roles')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Permisos directos del usuario</label>
        <div class="grid gap-2 sm:grid-cols-3">
            @foreach ($permissions as $permission)
                <label class="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        name="permissions[]"
                        value="{{ $permission->name }}"
                        class="checkbox checkbox-primary"
                        @checked(collect(old('permissions', $user->permissions->pluck('name')->all() ?? []))->contains($permission->name))
                    >
                    <span>{{ $permissionLabels[$permission->name] ?? $permission->name }}</span>
                </label>
            @endforeach
        </div>
        @error('permissions')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('security.users.index') }}" class="btn btn-outline">Cancelar</a>
</div>
