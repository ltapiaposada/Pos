<?php

namespace App\Http\Requests;

use App\Models\RestaurantOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RestaurantOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_restaurant');
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    RestaurantOrder::STATUS_DELIVERED,
                    RestaurantOrder::STATUS_CANCELLED,
                ]),
            ],
        ];
    }
}
