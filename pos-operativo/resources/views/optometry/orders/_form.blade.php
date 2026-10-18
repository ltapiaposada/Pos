@csrf
@php
    $selectedPatientId = (int) old('customer_id', $order->customer_id ?? $selectedPatientId ?? 0);
    $selectedPatient = $patients->firstWhere('id', $selectedPatientId);
    $patientSearchLabel = $selectedPatient
        ? trim($selectedPatient->name.($selectedPatient->document ? ' - '.$selectedPatient->document : ''))
        : '';
    $selectedClinicalRecordId = (int) old('clinical_record_id', $order->clinical_record_id ?? $record?->id ?? 0);
@endphp

<div
    x-data="{
        patients: @js($patients->map(fn ($patient) => [
            'id' => $patient->id,
            'name' => $patient->name,
            'document' => $patient->document,
            'label' => trim($patient->name.($patient->document ? ' - '.$patient->document : '')),
        ])->values()->all()),
        clinicalRecords: @js($clinicalRecords->map(fn ($clinicalRecord) => [
            'id' => $clinicalRecord->id,
            'customer_id' => $clinicalRecord->customer_id,
            'label' => '#'.$clinicalRecord->id.' - '.optional($clinicalRecord->examined_at)->format('d/m/Y H:i').' - '.\Illuminate\Support\Str::limit((string) $clinicalRecord->reason_for_consultation, 60),
        ])->values()->all()),
        patientId: @js($selectedPatientId ? (string) $selectedPatientId : ''),
        patientSearch: @js($patientSearchLabel),
        filteredPatients: [],
        patientDropdownOpen: false,
        clinicalRecordId: @js($selectedClinicalRecordId ? (string) $selectedClinicalRecordId : ''),
        init() {
            this.filterPatients();
            this.ensureClinicalRecordMatchesPatient();
        },
        get filteredClinicalRecords() {
            if (!this.patientId) {
                return [];
            }
            return this.clinicalRecords.filter((clinicalRecord) => String(clinicalRecord.customer_id) === String(this.patientId));
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
            const previousPatientId = this.patientId;
            this.patientId = String(patient.id);
            this.patientSearch = patient.label;
            this.patientDropdownOpen = false;
            this.filterPatients();
            if (String(previousPatientId) !== String(this.patientId)) {
                this.ensureClinicalRecordMatchesPatient();
            }
        },
        clearIfMismatch() {
            const exact = this.patients.find((patient) => patient.label === this.patientSearch);
            if (exact) {
                const previousPatientId = this.patientId;
                this.patientId = String(exact.id);
                if (String(previousPatientId) !== String(this.patientId)) {
                    this.ensureClinicalRecordMatchesPatient();
                }
                return;
            }
            this.patientId = '';
            this.clinicalRecordId = '';
        },
        ensureClinicalRecordMatchesPatient() {
            if (!this.filteredClinicalRecords.some((clinicalRecord) => String(clinicalRecord.id) === String(this.clinicalRecordId))) {
                this.clinicalRecordId = '';
            }
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
        <input type="datetime-local" name="ordered_at" value="{{ old('ordered_at', optional($order->ordered_at ?? null)->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}" class="input input-bordered w-full" required>
    </div>
    <div>
        <label class="field-label">Estado</label>
        <select name="status" class="select select-bordered w-full" required>
            @foreach ($statusOptions as $statusValue => $statusLabel)
                <option value="{{ $statusValue }}" @selected(old('status', $order->status ?? \App\Models\MedicalOrder::STATUS_ACTIVE) === $statusValue)>{{ $statusLabel }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label">Historia clinica relacionada</label>
        <select name="clinical_record_id" x-model="clinicalRecordId" class="select select-bordered w-full">
            <option value="">Sin historia relacionada</option>
            <template x-for="clinicalRecord in filteredClinicalRecords" :key="clinicalRecord.id">
                <option :value="String(clinicalRecord.id)" x-text="clinicalRecord.label"></option>
            </template>
        </select>
        <p class="mt-1 text-xs text-base-content/60">Se muestran solo las historias del paciente seleccionado.</p>
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Formula o indicaciones</label>
        <textarea name="prescription_details" rows="6" class="textarea textarea-bordered w-full" required>{{ old('prescription_details', $order->prescription_details ?? '') }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Instrucciones de uso</label>
        <textarea name="usage_instructions" rows="4" class="textarea textarea-bordered w-full">{{ old('usage_instructions', $order->usage_instructions ?? '') }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <label class="field-label">Observaciones</label>
        <textarea name="observations" rows="4" class="textarea textarea-bordered w-full">{{ old('observations', $order->observations ?? '') }}</textarea>
    </div>
    <div>
        <label class="field-label">Profesional</label>
        <input name="professional_name" value="{{ old('professional_name', $order->professional_name ?? auth()->user()->name) }}" class="input input-bordered w-full">
    </div>
    <div>
        <label class="field-label">Registro profesional</label>
        <input name="professional_license" value="{{ old('professional_license', $order->professional_license ?? '') }}" class="input input-bordered w-full">
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('optometry.orders.index') }}" class="btn btn-outline">Cancelar</a>
</div>
