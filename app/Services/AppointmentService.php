<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Events\AppointmentStatusChanged;
use App\Jobs\SendAppointmentStatusNotification;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    /**
     * @param array<string, mixed> $data
     */
    public function book(User $customer, array $data): Appointment
    {
        return Appointment::query()->create([
            'user_id' => $customer->id,
            'vehicle_id' => $data['vehicle_id'],
            'service_id' => $data['service_id'],
            'scheduled_at' => $data['scheduled_at'],
            'customer_notes' => $data['customer_notes'] ?? null,
        ]);
    }

    public function updateStatus(Appointment $appointment, AppointmentStatus $status, ?string $notes, int $laborCents, int $partsCents): Appointment
    {
        return DB::transaction(function () use ($appointment, $status, $notes, $laborCents, $partsCents): Appointment {
            $appointment->update([
                'status' => $status,
                'admin_notes' => $notes,
            ]);

            if ($laborCents > 0 || $partsCents > 0) {
                $appointment->invoice()->updateOrCreate([], [
                    'labor_cents' => $laborCents,
                    'parts_cents' => $partsCents,
                ]);
            }

            SendAppointmentStatusNotification::dispatch($appointment->id);
            AppointmentStatusChanged::dispatch($appointment->fresh());

            return $appointment->fresh(['invoice']);
        });
    }
}
