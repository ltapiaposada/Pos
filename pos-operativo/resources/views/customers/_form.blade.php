@csrf

<div class="form-grid">
    <div class="sm:col-span-2">
        <label class="field-label">Nombre</label>
        <input name="name" value="{{ old('name', $customer->name ?? '') }}" class="input input-bordered w-full" required>
        @error('name')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="field-label">Documento</label>
        <input name="document" value="{{ old('document', $customer->document ?? '') }}" class="input input-bordered w-full">
    </div>
    <div>
        <label class="field-label">Email</label>
        <input name="email" type="email" value="{{ old('email', $customer->email ?? '') }}" class="input input-bordered w-full">
    </div>
    <div>
        <label class="field-label">Telefono</label>
        <input name="phone" value="{{ old('phone', $customer->phone ?? '') }}" class="input input-bordered w-full">
    </div>
    <div>
        <label class="field-label">Direccion</label>
        <input name="address" value="{{ old('address', $customer->address ?? '') }}" class="input input-bordered w-full">
    </div>
    <div>
        <label class="field-label">Tipo de contacto</label>
        <select name="contact_type" class="select select-bordered w-full" required>
            <option value="person" @selected(old('contact_type', $customer->contact_type ?? 'person') === 'person')>Persona</option>
            <option value="company" @selected(old('contact_type', $customer->contact_type ?? 'person') === 'company')>Empresa</option>
            <option value="supplier" @selected(old('contact_type', $customer->contact_type ?? 'person') === 'supplier')>Proveedor</option>
        </select>
        @error('contact_type')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="field-label">Activo</label>
        <select name="is_active" class="select select-bordered w-full" required>
            <option value="1" @selected(old('is_active', $customer->is_active ?? true))>Si</option>
            <option value="0" @selected(old('is_active', $customer->is_active ?? true) === false)>No</option>
        </select>
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('customers.index') }}" class="btn btn-outline">Cancelar</a>
</div>
