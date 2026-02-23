<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PurchaseRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $items = $this->input('items');

        if (is_string($items)) {
            $decoded = json_decode($items, true);
            $items = is_array($decoded) ? $decoded : [];
        }

        $this->merge([
            'items' => $items,
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()->can('manage_purchases');
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'contact_id' => ['nullable', 'integer', 'exists:customers,id'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'supplier_document' => ['nullable', 'string', 'max:100'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cash,transfer,credit'],
            'paid_total' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $items = collect($this->input('items', []));
            if ($items->isEmpty()) {
                return;
            }

            $productIds = $items->pluck('product_id');
            if ($productIds->count() !== $productIds->unique()->count()) {
                $validator->errors()->add('items', 'No puedes repetir productos en una misma compra.');
            }

            if ($this->filled('contact_id') && Customer::supportsContactType()) {
                $contact = Customer::query()->find((int) $this->input('contact_id'));
                if (! $contact || ! $contact->is_active || $contact->contact_type !== Customer::TYPE_SUPPLIER) {
                    $validator->errors()->add('contact_id', 'Selecciona un contacto de tipo proveedor activo.');
                }
            }
        });
    }
}
