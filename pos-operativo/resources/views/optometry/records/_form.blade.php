@csrf
@php
    $selectedPatientId = (int) old('customer_id', $record->customer_id ?? $selectedPatientId ?? 0);
    $selectedPatient = $patients->firstWhere('id', $selectedPatientId);
    $patientSearchLabel = $selectedPatient
        ? trim($selectedPatient->name.($selectedPatient->document ? ' - '.$selectedPatient->document : ''))
        : '';
@endphp

<div
    x-data="{
        patients: @js($patients->map(fn ($patient) => [
            'id' => $patient->id,
            'name' => $patient->name,
            'document' => $patient->document,
            'label' => trim($patient->name.($patient->document ? ' - '.$patient->document : '')),
        ])->values()->all()),
        patientId: @js($selectedPatientId ? (string) $selectedPatientId : ''),
        patientSearch: @js($patientSearchLabel),
        filteredPatients: [],
        patientDropdownOpen: false,
        init() {
            this.filterPatients();
        },
        filterPatients() {
            const term = String(this.patientSearch || '').trim().toLowerCase();
            if (term === '') {
                this.filteredPatients = this.patients.slice(0, 8);
                return;
            }
            this.filteredPatients = this.patients
                .filter((patient) => patient.label.toLowerCase().includes(term))
                .slice(0, 8);
        },
        selectPatient(patient) {
            this.patientId = String(patient.id);
            this.patientSearch = patient.label;
            this.patientDropdownOpen = false;
            this.filterPatients();
        },
        clearIfMismatch() {
            const exact = this.patients.find((patient) => patient.label === this.patientSearch);
            this.patientId = exact ? String(exact.id) : '';
        },
    }"
    class="form-grid"
>
    <div class="relative" @click.outside="patientDropdownOpen = false">
        <label class="field-label">Paciente</label>
        <input type="hidden" name="customer_id" x-model="patientId">
        <input
            x-model="patientSearch"
            @focus="patientDropdownOpen = true; filterPatients()"
            @input="patientDropdownOpen = true; filterPatients(); clearIfMismatch()"
            @blur="clearIfMismatch()"
            class="input input-bordered w-full"
            placeholder="Busca por nombre o documento"
            autocomplete="off"
            required
        >
        <div
            x-show="patientDropdownOpen"
            x-cloak
            class="absolute z-40 mt-1 max-h-60 w-full overflow-y-auto rounded-xl border border-base-300 bg-base-100 shadow-lg"
            style="display: none;"
        >
            <template x-for="patient in filteredPatients" :key="patient.id">
                <button
                    type="button"
                    @click="selectPatient(patient)"
                    class="w-full px-3 py-2 text-left text-sm hover:bg-base-200"
                    x-text="patient.label"
                ></button>
            </template>
            <div x-show="filteredPatients.length === 0" class="px-3 py-2 text-xs text-base-content/60">Sin coincidencias.</div>
        </div>
        @error('customer_id')
            <p class="mt-1 text-xs text-error">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="field-label">Fecha y hora</label>
        <input type="datetime-local" name="examined_at" value="{{ old('examined_at', optional($record->examined_at ?? null)->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}" class="input input-bordered w-full" required>
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Motivo de consulta</label>
        <textarea name="reason_for_consultation" rows="3" class="textarea textarea-bordered w-full" required>{{ old('reason_for_consultation', $record->reason_for_consultation ?? '') }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Antecedentes medicos</label>
        <textarea name="medical_history" rows="4" class="textarea textarea-bordered w-full">{{ old('medical_history', $record->medical_history ?? '') }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Antecedentes oculares</label>
        <textarea name="ocular_history" rows="4" class="textarea textarea-bordered w-full">{{ old('ocular_history', $record->ocular_history ?? '') }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Examen</label>
        <textarea name="examination" rows="5" class="textarea textarea-bordered w-full">{{ old('examination', $record->examination ?? '') }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Diagnostico</label>
        <textarea name="diagnosis" rows="4" class="textarea textarea-bordered w-full">{{ old('diagnosis', $record->diagnosis ?? '') }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Conducta o plan</label>
        <textarea name="treatment_plan" rows="4" class="textarea textarea-bordered w-full">{{ old('treatment_plan', $record->treatment_plan ?? '') }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Observaciones</label>
        <textarea name="observations" rows="3" class="textarea textarea-bordered w-full">{{ old('observations', $record->observations ?? '') }}</textarea>
    </div>
    <div>
        <label class="field-label">Profesional</label>
        <input name="professional_name" value="{{ old('professional_name', $record->professional_name ?? auth()->user()->name) }}" class="input input-bordered w-full">
    </div>
    <div>
        <label class="field-label">Registro profesional</label>
        <input name="professional_license" value="{{ old('professional_license', $record->professional_license ?? '') }}" class="input input-bordered w-full">
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('optometry.records.index') }}" class="btn btn-outline">Cancelar</a>
</div>
