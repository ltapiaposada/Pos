<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClinicalRecordRequest;
use App\Models\ClinicalRecord;
use App\Models\Customer;
use Illuminate\Http\Request;

class ClinicalRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = ClinicalRecord::query()->with('customer')->latest('examined_at');

        if ($search = trim((string) $request->get('q', ''))) {
            $query->whereHas('customer', function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('document', 'like', "%{$search}%");
            });
        }

        $records = $query->paginate(15)->withQueryString();

        return view('optometry.records.index', compact('records'));
    }

    public function create(Request $request)
    {
        $patients = Customer::query()
            ->where('contact_type', Customer::TYPE_PERSON)
            ->whereHas('optometryProfile')
            ->orderBy('name')
            ->get(['id', 'name', 'document']);
        $selectedPatientId = $request->integer('patient_id') ?: null;

        return view('optometry.records.create', compact('patients', 'selectedPatientId'));
    }

    public function store(ClinicalRecordRequest $request)
    {
        $record = ClinicalRecord::query()->create([
            ...$request->validated(),
            'created_by_user_id' => $request->user()->id,
        ]);

        return redirect()->route('optometry.records.show', $record)->with('status', 'Historia clinica creada.');
    }

    public function show(ClinicalRecord $record)
    {
        $record->load(['customer', 'createdBy', 'medicalOrders']);

        return view('optometry.records.show', compact('record'));
    }

    public function edit(ClinicalRecord $record)
    {
        $patients = Customer::query()
            ->where('contact_type', Customer::TYPE_PERSON)
            ->whereHas('optometryProfile')
            ->orderBy('name')
            ->get(['id', 'name', 'document']);

        return view('optometry.records.edit', compact('record', 'patients'));
    }

    public function update(ClinicalRecordRequest $request, ClinicalRecord $record)
    {
        $record->update($request->validated());

        return redirect()->route('optometry.records.show', $record)->with('status', 'Historia clinica actualizada.');
    }

    public function print(ClinicalRecord $record)
    {
        $record->load(['customer', 'createdBy']);

        return view('optometry.records.print', compact('record'));
    }
}
