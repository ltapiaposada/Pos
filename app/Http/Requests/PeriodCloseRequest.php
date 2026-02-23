<?php

namespace App\Http\Requests;

use Illuminate\Support\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PeriodCloseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_accounting');
    }

    public function rules(): array
    {
        return [
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'entry_date' => ['required', 'date', 'after_or_equal:to_date'],
            'description' => ['required', 'string', 'max:255'],
            'equity_account_code' => ['required', 'string', 'max:30'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->from_date && $this->to_date) {
                $days = Carbon::parse($this->from_date)->diffInDays(Carbon::parse($this->to_date));
                if ($days > 370) {
                    $validator->errors()->add('to_date', 'El cierre no debe superar 370 dias por operacion.');
                }
            }
        });
    }
}
