<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'make' => ['required', 'string', 'max:80'],
            'model' => ['required', 'string', 'max:80'],
            'year' => ['nullable', 'integer', 'between:1980,2035'],
            'registration_number' => ['nullable', 'string', 'max:20'],
            'vin' => ['nullable', 'string', 'max:40'],
        ];
    }
}
