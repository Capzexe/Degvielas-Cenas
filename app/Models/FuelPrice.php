<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelPrice extends Model
{
    use HasFactory;

    protected $table = 'prices';

    protected $fillable = [
        'station_id',
        'fuel_type',
        'price',
        'source_type',
        'source_label',
        'source_url',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:3',
            'fetched_at' => 'datetime',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}
