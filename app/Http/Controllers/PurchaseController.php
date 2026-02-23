<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use RuntimeException;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::query()
            ->with(['branch', 'user'])
            ->orderByDesc('purchased_at')
            ->orderByDesc('id');

        if ($search = $request->get('q')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('purchase_number', 'like', "%{$search}%")
                    ->orWhere('supplier_name', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%");
            });
        }

        if ($branchId = $request->get('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        $purchases = $query->paginate(20)->withQueryString();
        $branches = Branch::query()->orderBy('name')->get(['id', 'name']);

        return view('purchases.index', compact('purchases', 'branches'));
    }

    public function create(Request $request)
    {
        $branches = Branch::query()->orderBy('name')->get(['id', 'name']);
        $branchId = $request->get('branch_id', $request->user()->branch_id ?? $branches->first()?->id);
        $contacts = Customer::query()
            ->where('is_active', true)
            ->when(
                Customer::supportsContactType(),
                fn ($query) => $query->where('contact_type', Customer::TYPE_SUPPLIER)
            )
            ->orderBy('name')
            ->get(
                Customer::supportsContactType()
                    ? ['id', 'name', 'document', 'contact_type']
                    : ['id', 'name', 'document']
            );
        $products = Product::query()
            ->with('tax:id,rate')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'cost_price', 'tax_id']);

        $productCatalog = $products->map(function (Product $product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'cost_price' => (float) $product->cost_price,
                'tax_rate' => (float) ($product->tax?->rate ?? 0),
            ];
        })->values();

        return view('purchases.create', compact('branches', 'branchId', 'productCatalog', 'contacts'));
    }

    public function store(PurchaseRequest $request, PurchaseService $service)
    {
        $supplierName = $request->input('supplier_name');
        $supplierDocument = $request->input('supplier_document');
        $contactId = $request->input('contact_id');

        if ($contactId) {
            $contact = Customer::query()
                ->where('is_active', true)
                ->when(
                    Customer::supportsContactType(),
                    fn ($query) => $query->where('contact_type', Customer::TYPE_SUPPLIER)
                )
                ->find($contactId);

            if ($contact) {
                $supplierName = $contact->name;
                $supplierDocument = $contact->document;
            }
        }

        try {
            $purchase = $service->createPurchase([
                'branch_id' => $request->integer('branch_id'),
                'supplier_name' => $supplierName,
                'supplier_document' => $supplierDocument,
                'invoice_number' => $request->input('invoice_number'),
                'items' => $request->input('items'),
                'payment_method' => $request->input('payment_method'),
                'paid_total' => $request->input('paid_total', 0),
                'notes' => $request->input('notes'),
            ], $request->user()->id);
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors([
                'purchase' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('purchases.show', $purchase)->with('status', 'Compra registrada.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['items', 'branch', 'user', 'payments.user']);

        return view('purchases.show', compact('purchase'));
    }
}
