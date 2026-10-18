@csrf

<div class="form-grid">
    <div>
        <label class="field-label">Nombre</label>
        <input name="name" value="{{ old('name', $branch->name ?? '') }}" class="input input-bordered w-full" required>
        @error('name')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="field-label">Codigo</label>
        <input name="code" value="{{ old('code', $branch->code ?? '') }}" class="input input-bordered w-full" required>
        @error('code')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Direccion</label>
        <input name="address" value="{{ old('address', $branch->address ?? '') }}" class="input input-bordered w-full">
        @error('address')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="field-label">Telefono</label>
        <input name="phone" value="{{ old('phone', $branch->phone ?? '') }}" class="input input-bordered w-full">
        @error('phone')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('branches.index') }}" class="btn btn-outline">Cancelar</a>
</div>
