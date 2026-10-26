<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PriceHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fuel_type' => ['required', 'string', Rule::in(['95', '98', 'Diesel', 'LPG'])],
            'days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ];
    }
}
