@csrf

<div class="form-grid">
    <div class="sm:col-span-2">
        <label class="field-label">Nombre</label>
        <input name="name" value="{{ old('name', $category->name ?? '') }}" class="input input-bordered w-full" required>
        @error('name')
            <p class="text-xs text-error mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="field-label">Padre</label>
        <select name="parent_id" class="select select-bordered w-full">
            <option value="">Sin padre</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id ?? null) == $parent->id)>{{ $parent->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label">Descripcion</label>
        <input name="description" value="{{ old('description', $category->description ?? '') }}" class="input input-bordered w-full">
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('categories.index') }}" class="btn btn-outline">Cancelar</a>
</div>
