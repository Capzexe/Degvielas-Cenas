<?php

namespace App\Http\Requests;

use App\Enums\AppointmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() === true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(AppointmentStatus::class)],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
            'labor_eur' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'parts_eur' => ['nullable', 'numeric', 'min:0', 'max:99999'],
        ];
    }
}
