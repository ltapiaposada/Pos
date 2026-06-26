@csrf

<div class="form-grid">
    <div class="sm:col-span-2">
        <label class="field-label">Nombre completo</label>
        <input name="name" value="{{ old('name', $patient->name ?? '') }}" class="input input-bordered w-full" required>
        @error('name')
            <p class="mt-1 text-xs text-error">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="field-label">Documento</label>
        <input name="document" value="{{ old('document', $patient->document ?? '') }}" class="input input-bordered w-full">
    </div>
    <div>
        <label class="field-label">Fecha de nacimiento</label>
        <input type="date" name="birth_date" value="{{ old('birth_date', optional($patient->optometryProfile->birth_date ?? null)->format('Y-m-d')) }}" class="input input-bordered w-full">
    </div>
    <div>
        <label class="field-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $patient->email ?? '') }}" class="input input-bordered w-full">
    </div>
    <div>
        <label class="field-label">Telefono</label>
        <input name="phone" value="{{ old('phone', $patient->phone ?? '') }}" class="input input-bordered w-full">
    </div>
    <div>
        <label class="field-label">Genero</label>
        <select name="gender" class="select select-bordered w-full">
            <option value="">Sin especificar</option>
            <option value="female" @selected(old('gender', $patient->optometryProfile->gender ?? '') === 'female')>Femenino</option>
            <option value="male" @selected(old('gender', $patient->optometryProfile->gender ?? '') === 'male')>Masculino</option>
            <option value="other" @selected(old('gender', $patient->optometryProfile->gender ?? '') === 'other')>Otro</option>
        </select>
    </div>
    <div>
        <label class="field-label">Ocupacion</label>
        <input name="occupation" value="{{ old('occupation', $patient->optometryProfile->occupation ?? '') }}" class="input input-bordered w-full">
    </div>
    <div>
        <label class="field-label">Contacto de emergencia</label>
        <input name="emergency_contact_name" value="{{ old('emergency_contact_name', $patient->optometryProfile->emergency_contact_name ?? '') }}" class="input input-bordered w-full">
    </div>
    <div>
        <label class="field-label">Telefono emergencia</label>
        <input name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $patient->optometryProfile->emergency_contact_phone ?? '') }}" class="input input-bordered w-full">
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Direccion</label>
        <input name="address" value="{{ old('address', $patient->address ?? '') }}" class="input input-bordered w-full">
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Alergias</label>
        <textarea name="allergies" rows="3" class="textarea textarea-bordered w-full">{{ old('allergies', $patient->optometryProfile->allergies ?? '') }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Antecedentes sistemicos</label>
        <textarea name="systemic_history" rows="4" class="textarea textarea-bordered w-full">{{ old('systemic_history', $patient->optometryProfile->systemic_history ?? '') }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Antecedentes oculares</label>
        <textarea name="ocular_history" rows="4" class="textarea textarea-bordered w-full">{{ old('ocular_history', $patient->optometryProfile->ocular_history ?? '') }}</textarea>
    </div>
    <div>
        <label class="field-label">Activo</label>
        <select name="is_active" class="select select-bordered w-full" required>
            <option value="1" @selected(old('is_active', $patient->is_active ?? true))>Si</option>
            <option value="0" @selected((string) old('is_active', $patient->is_active ?? true) === '0')>No</option>
        </select>
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('optometry.patients.index') }}" class="btn btn-outline">Cancelar</a>
</div>
