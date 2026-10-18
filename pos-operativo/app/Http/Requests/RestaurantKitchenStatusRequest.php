<?php

namespace App\Http\Requests;

use App\Models\RestaurantOrderItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RestaurantKitchenStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_restaurant_kitchen');
    }

    public function rules(): array
    {
        return [
            'kitchen_status' => [
                'required',
                Rule::in([
                    RestaurantOrderItem::STATUS_IN_PREPARATION,
                    RestaurantOrderItem::STATUS_READY,
                    RestaurantOrderItem::STATUS_DELIVERED,
                ]),
            ],
        ];
    }
}
