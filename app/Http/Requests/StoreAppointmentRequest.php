<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAppointmentRequest extends FormRequest
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
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $vehicleBelongsToUser = Vehicle::query()
                    ->whereKey($this->integer('vehicle_id'))
                    ->where('user_id', $this->user()->id)
                    ->exists();

                if (! $vehicleBelongsToUser) {
                    $validator->errors()->add('vehicle_id', 'Sis auto nepieder pieslegtajam lietotajam.');
                }

                $slotTaken = Appointment::query()
                    ->where('scheduled_at', $this->date('scheduled_at'))
                    ->whereNot('status', 'cancelled')
                    ->exists();

                if ($slotTaken) {
                    $validator->errors()->add('scheduled_at', 'Sis laiks jau ir aiznemts.');
                }

                $scheduledAt = $this->date('scheduled_at');

                if ($scheduledAt?->isWeekend()) {
                    $validator->errors()->add('scheduled_at', 'Brivdienas serviss nepienem pierakstus.');
                }
            },
        ];
    }
}
