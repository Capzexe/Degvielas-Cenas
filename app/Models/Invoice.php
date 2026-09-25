<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'labor_cents',
        'parts_cents',
        'payment_status',
        'notes',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function totalCents(): int
    {
        return $this->labor_cents + $this->parts_cents;
    }

    public function formattedTotal(): string
    {
        return number_format($this->totalCents() / 100, 2).' EUR';
    }
}
