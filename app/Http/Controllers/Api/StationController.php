<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StationController extends Controller
{
    /**
     * Return stations with their latest known prices, sorted by the selected
     * fuel price when a fuel_type filter is present.
     */
    public function cheapest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fuel_type' => ['nullable', Rule::in(['95', '98', 'Diesel', 'LPG'])],
        ]);

        $fuelType = $validated['fuel_type'] ?? null;

        $latestPriceIds = DB::table('prices')
            ->selectRaw('MAX(id)')
            ->groupBy('station_id', 'fuel_type');

        $stations = Station::query()
            ->with(['latestPrices' => fn ($query) => $query->orderBy('fuel_type')])
            ->when($fuelType, function ($query) use ($fuelType, $latestPriceIds): void {
                $query
                    ->select('stations.*')
                    ->join('prices as selected_prices', 'selected_prices.station_id', '=', 'stations.id')
                    ->where('selected_prices.fuel_type', $fuelType)
                    ->whereIn('selected_prices.id', $latestPriceIds)
                    ->addSelect('selected_prices.price as selected_fuel_price')
                    ->orderBy('selected_fuel_price');
            })
            ->when(! $fuelType, fn ($query) => $query->orderBy('brand')->orderBy('name'))
            ->get();

        return response()->json([
            'data' => $stations->map(function (Station $station) use ($fuelType): array {
                $selectedPrice = $fuelType !== null
                    ? $station->latestPrices->firstWhere('fuel_type', $fuelType)
                    : null;

                return [
                    'id' => $station->id,
                    'name' => $station->name,
                    'brand' => $station->brand,
                    'address' => $station->address,
                    'latitude' => (float) $station->latitude,
                    'longitude' => (float) $station->longitude,
                    'map_location_exact' => $this->hasExactMapLocation($station, $selectedPrice?->source_type),
                    'selected_fuel_price' => $station->selected_fuel_price !== null
                        ? (float) $station->selected_fuel_price
                        : null,
                    'latest_prices' => $station->latestPrices->map(fn ($price): array => [
                        'fuel_type' => $price->fuel_type,
                        'price' => (float) $price->price,
                        'source_type' => $price->source_type,
                        'source_label' => $price->source_label,
                        'source_url' => $price->source_url,
                        'fetched_at' => $price->fetched_at?->toIso8601String(),
                    ])->values(),
                ];
            })->values(),
        ]);
    }

    private function hasExactMapLocation(Station $station, ?string $sourceType): bool
    {
        if (str_starts_with($station->address, 'Oficiālā cenu lapa:')) {
            return false;
        }

        return in_array($sourceType, ['official_station', 'user_reported'], true);
    }
}
