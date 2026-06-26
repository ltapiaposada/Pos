<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicalOrderRequest;
use App\Models\ClinicalRecord;
use App\Models\Customer;
use App\Models\MedicalOrder;
use Illuminate\Http\Request;

class MedicalOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = MedicalOrder::query()->with(['customer', 'clinicalRecord'])->latest('ordered_at');

        if ($search = trim((string) $request->get('q', ''))) {
            $query->whereHas('customer', function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('document', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(15)->withQueryString();
        $statusOptions = MedicalOrder::statusOptions();

        return view('optometry.orders.index', compact('orders', 'statusOptions'));
    }

    public function create(Request $request)
    {
        $patients = Customer::query()
            ->where('contact_type', Customer::TYPE_PERSON)
            ->whereHas('optometryProfile')
            ->orderBy('name')
            ->get(['id', 'name', 'document']);
        $clinicalRecords = ClinicalRecord::query()
            ->with('customer:id,name,document')
            ->latest('examined_at')
            ->get(['id', 'customer_id', 'examined_at', 'reason_for_consultation']);
        $selectedPatientId = $request->integer('patient_id') ?: null;
        $record = null;

        if ($recordId = $request->integer('clinical_record_id')) {
            $record = ClinicalRecord::query()->with('customer')->findOrFail($recordId);
            $selectedPatientId = $record->customer_id;
        }

        $statusOptions = MedicalOrder::statusOptions();

        return view('optometry.orders.create', compact('patients', 'clinicalRecords', 'selectedPatientId', 'record', 'statusOptions'));
    }

    public function store(MedicalOrderRequest $request)
    {
        $order = MedicalOrder::query()->create([
            ...$request->validated(),
            'created_by_user_id' => $request->user()->id,
        ]);

        return redirect()->route('optometry.orders.show', $order)->with('status', 'Orden medica creada.');
    }

    public function show(MedicalOrder $order)
    {
        $order->load(['customer', 'clinicalRecord', 'createdBy', 'sale']);
        $statusOptions = MedicalOrder::statusOptions();

        return view('optometry.orders.show', compact('order', 'statusOptions'));
    }

    public function edit(MedicalOrder $order)
    {
        $patients = Customer::query()
            ->where('contact_type', Customer::TYPE_PERSON)
            ->whereHas('optometryProfile')
            ->orderBy('name')
            ->get(['id', 'name', 'document']);
        $clinicalRecords = ClinicalRecord::query()
            ->with('customer:id,name,document')
            ->latest('examined_at')
            ->get(['id', 'customer_id', 'examined_at', 'reason_for_consultation']);
        $statusOptions = MedicalOrder::statusOptions();
        $record = $order->clinicalRecord;

        return view('optometry.orders.edit', compact('order', 'patients', 'clinicalRecords', 'statusOptions', 'record'));
    }

    public function update(MedicalOrderRequest $request, MedicalOrder $order)
    {
        $order->update($request->validated());

        return redirect()->route('optometry.orders.show', $order)->with('status', 'Orden medica actualizada.');
    }

    public function print(MedicalOrder $order)
    {
        $order->load(['customer', 'clinicalRecord', 'createdBy']);

        return view('optometry.orders.print', compact('order'));
    }
}
