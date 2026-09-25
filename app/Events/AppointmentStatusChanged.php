<?php

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentStatusChanged implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('appointments.'.$this->appointment->id);
    }

    /**
     * @return array<string, string>
     */
    public function broadcastWith(): array
    {
        return [
            'status' => $this->appointment->status->value,
            'label' => $this->appointment->status->label(),
        ];
    }
}
