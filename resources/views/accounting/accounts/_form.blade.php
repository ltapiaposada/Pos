@csrf

<div class="form-grid">
    <div>
        <label class="field-label">Codigo PUC</label>
        <input name="code" value="{{ old('code', $account->code ?? '') }}" class="input input-bordered w-full" required>
        @error('code')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Nombre</label>
        <input name="name" value="{{ old('name', $account->name ?? '') }}" class="input input-bordered w-full" required>
        @error('name')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="field-label">Tipo</label>
        <select name="type" class="select select-bordered w-full" required>
            @foreach (['asset' => 'Activo', 'liability' => 'Pasivo', 'equity' => 'Patrimonio', 'income' => 'Ingreso', 'expense' => 'Gasto'] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $account->type ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label">Naturaleza</label>
        <select name="nature" class="select select-bordered w-full" required>
            <option value="debit" @selected(old('nature', $account->nature ?? '') === 'debit')>Debito</option>
            <option value="credit" @selected(old('nature', $account->nature ?? '') === 'credit')>Credito</option>
        </select>
    </div>
    <div>
        <label class="field-label">Cuenta padre</label>
        <select name="parent_account_id" class="select select-bordered w-full">
            <option value="">Sin padre</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected((string) old('parent_account_id', $account->parent_account_id ?? '') === (string) $parent->id)>
                    {{ $parent->code }} - {{ $parent->name }}
                </option>
            @endforeach
        </select>
        @error('parent_account_id')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="field-label">Permite movimientos</label>
        <select name="is_postable" class="select select-bordered w-full" required>
            <option value="1" @selected(old('is_postable', $account->is_postable ?? true))>Si</option>
            <option value="0" @selected((string) old('is_postable', $account->is_postable ?? true) === '0')>No</option>
        </select>
    </div>
    <div>
        <label class="field-label">Activa</label>
        <select name="is_active" class="select select-bordered w-full" required>
            <option value="1" @selected(old('is_active', $account->is_active ?? true))>Si</option>
            <option value="0" @selected((string) old('is_active', $account->is_active ?? true) === '0')>No</option>
        </select>
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('accounting.accounts.index') }}" class="btn btn-outline">Cancelar</a>
</div>

