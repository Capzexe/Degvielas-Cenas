<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PriceHistoryRequest;
use App\Models\FuelPrice;
use Illuminate\Http\JsonResponse;

class PriceHistoryController extends Controller
{
    public function index(PriceHistoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $fuelType = $validated['fuel_type'];
        $days = (int) ($validated['days'] ?? 90);

        $prices = FuelPrice::query()
            ->with('station:id,name,brand')
            ->where('fuel_type', $fuelType)
            ->where('source_type', '!=', 'test_data')
            ->where('fetched_at', '>=', now()->subDays($days))
            ->whereBetween('price', $this->plausibleRange($fuelType))
            ->whereHas('station')
            ->orderByDesc('fetched_at')
            ->orderByDesc('id')
            ->limit(10001)
            ->get();

        $truncated = $prices->count() > 10000;
        $prices = $prices->take(10000)->reverse()->values();

        $series = $prices
            ->groupBy(fn (FuelPrice $price) => $price->station->brand.' - '.$price->station->name)
            ->map(fn ($items, string $name): array => [
                'name' => $name,
                'brand' => $items->first()->station->brand,
                'source_label' => $items->last()->source_label,
                'points' => $items->map(fn (FuelPrice $price): array => [
                    'date' => $price->fetched_at?->toIso8601String(),
                    'price' => (float) $price->price,
                ])->values(),
            ])
            ->filter(fn (array $series): bool => count($series['points']) > 0)
            ->sortByDesc(fn (array $series): int => count($series['points']))
            ->take(6)
            ->values();

        return response()->json([
            'fuel_type' => $fuelType,
            'days' => $days,
            'truncated' => $truncated,
            'series' => $series,
        ]);
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function plausibleRange(string $fuelType): array
    {
        return match ($fuelType) {
            '95', '98' => [1.35, 3.00],
            'Diesel' => [1.45, 3.00],
            'LPG' => [0.45, 1.30],
        };
    }
}
