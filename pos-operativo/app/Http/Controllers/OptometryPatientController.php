<?php

namespace App\Http\Controllers;

use App\Http\Requests\OptometryPatientRequest;
use App\Models\Customer;
use App\Models\OptometryPatientProfile;
use Illuminate\Http\Request;

class OptometryPatientController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()
            ->with('optometryProfile')
            ->where('contact_type', Customer::TYPE_PERSON)
            ->whereHas('optometryProfile');

        if ($search = trim((string) $request->get('q', ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('document', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $patients = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('optometry.patients.index', compact('patients'));
    }

    public function create()
    {
        return view('optometry.patients.create');
    }

    public function store(OptometryPatientRequest $request)
    {
        $patient = Customer::query()->create($request->patientData());
        $this->syncProfile($patient, $request->profileData());

        return redirect()->route('optometry.patients.show', $patient)->with('status', 'Paciente creado.');
    }

    public function show(Customer $patient)
    {
        abort_unless($patient->optometryProfile()->exists(), 404);

        $patient->load([
            'optometryProfile',
            'clinicalRecords' => fn ($query) => $query->latest('examined_at')->limit(10),
            'medicalOrders' => fn ($query) => $query->latest('ordered_at')->limit(10),
        ]);

        return view('optometry.patients.show', compact('patient'));
    }

    public function edit(Customer $patient)
    {
        abort_unless($patient->optometryProfile()->exists(), 404);
        $patient->load('optometryProfile');

        return view('optometry.patients.edit', compact('patient'));
    }

    public function update(OptometryPatientRequest $request, Customer $patient)
    {
        abort_unless($patient->optometryProfile()->exists(), 404);

        $patient->update($request->patientData());
        $this->syncProfile($patient, $request->profileData());

        return redirect()->route('optometry.patients.show', $patient)->with('status', 'Paciente actualizado.');
    }

    private function syncProfile(Customer $patient, array $data): void
    {
        OptometryPatientProfile::query()->updateOrCreate(
            ['customer_id' => $patient->id],
            $data
        );
    }
}
