<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'address',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function prices(): HasMany
    {
        return $this->hasMany(FuelPrice::class);
    }

    public function latestPrice(): HasOne
    {
        return $this->hasOne(FuelPrice::class)->latestOfMany('fetched_at');
    }

    public function latestPrices(): HasMany
    {
        return $this->prices()
            ->whereIn('id', function ($query): void {
                $query->selectRaw('MAX(id)')
                    ->from('prices')
                    ->groupBy('station_id', 'fuel_type');
            });
    }
}
