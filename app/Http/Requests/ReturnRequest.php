<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('process_return');
    }

    public function rules(): array
    {
        return [
            'sale_id' => ['required', 'integer', 'exists:sales,id'],
            'reason' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $hasPositiveQty = collect($this->input('items', []))
                ->contains(fn ($item) => (float) ($item['quantity'] ?? 0) > 0);

            if (! $hasPositiveQty) {
                $validator->errors()->add('items', 'Debes indicar cantidad mayor a cero en al menos un item.');
            }
        });
    }
}
