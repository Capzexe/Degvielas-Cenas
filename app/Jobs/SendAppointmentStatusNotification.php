<?php

namespace App\Jobs;

use App\Models\Appointment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendAppointmentStatusNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $appointmentId)
    {
    }

    public function handle(): void
    {
        $appointment = Appointment::with(['user', 'vehicle'])->findOrFail($this->appointmentId);

        Log::info('Appointment status notification', [
            'customer' => $appointment->user->email,
            'vehicle' => $appointment->vehicle->title(),
            'status' => $appointment->status->value,
        ]);
    }
}
