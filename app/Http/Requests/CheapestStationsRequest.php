<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheapestStationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // These are intentionally public, read-only prices.
    }

    public function rules(): array
    {
        return ['fuel_type' => ['nullable', 'string', Rule::in(['95', '98', 'Diesel', 'LPG'])]];
    }
}
