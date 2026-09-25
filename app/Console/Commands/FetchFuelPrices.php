<?php

namespace App\Console\Commands;

use App\Models\FuelPrice;
use App\Models\Station;
use App\Services\FuelPrices\LatvianFuelPriceSources;
use App\Services\FuelPrices\OfficialFuelPricePage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Throwable;

class FetchFuelPrices extends Command
{
    protected $signature = 'fetch:fuel-prices
        {--debug-source= : Saglabāt norādītā zīmola saņemto lapas tekstu storage/app/debug}';

    protected $description = 'Ielasa Latvijas degvielas cenas no oficiālajām publiskajām lapām, kur tas ir iespējams.';

    public function handle(OfficialFuelPricePage $pricePage, LatvianFuelPriceSources $sources): int
    {
        $fetchedAt = Carbon::now();
        $this->seedStations($sources);
        $savedPrices = 0;
        $this->pruneImportedPrices($sources);
        $this->removeUnsupportedBrands();
        $this->removeImpreciseStationMappedOfficialPrices();
        $this->removePublishedPriceStations();
        $this->clearDebugFile();

        foreach ($sources->officialSources() as $source) {
            $prices = [];
            $stationPrices = [];

            foreach ($this->urlsFor($source) as $url) {
                try {
                    $stationPrices = $pricePage->stationPrices(
                        $url,
                        $source,
                        $this->option('debug-source') === $source['brand'] ? $source['brand'] : null,
                    );

                    if ($stationPrices !== []) {
                        $source['source_url'] = $url;

                        break;
                    }

                    $prices = $pricePage->prices(
                        $url,
                        $source['fuel_labels'],
                        $this->option('debug-source') === $source['brand'] ? $source['brand'] : null,
                    );

                    if ($prices !== []) {
                        $source['source_url'] = $url;

                        break;
                    }
                } catch (Throwable $exception) {
                    $this->warn($source['brand'].': neizdevās ielasīt lapu - '.$exception->getMessage());
                }
            }

            if ($stationPrices !== []) {
                $savedForSource = $this->saveStationPrices($stationPrices, $source, $fetchedAt);
                $savedPrices += $savedForSource;

                $this->info($source['brand'].': saglabātas '.$savedForSource.' cenas konkrētās stacijās.');

                continue;
            }

            if ($prices === []) {
                $this->warn($source['brand'].': cenas netika atrastas oficiālajā lapā.');

                continue;
            }

            $stations = $this->priceStationsForSource($source);

            foreach ($stations as $station) {
                foreach ($prices as $fuelType => $price) {
                    FuelPrice::query()->create([
                        'station_id' => $station->id,
                        'fuel_type' => $fuelType,
                        'price' => $price,
                        'source_type' => $source['source_type'],
                        'source_label' => $source['source_label'],
                        'source_url' => $source['source_url'],
                        'fetched_at' => $fetchedAt,
                    ]);

                    $savedPrices++;
                }
            }

            $this->info($source['brand'].': saglabātas '.count($prices).' degvielas cenas.');
        }

        $this->line('Neste: oficiāla tiešsaistes cenu plūsma nav pieejama; paredzēts lietotāju ziņojumiem.');
        $this->info('Kopā saglabātas '.$savedPrices.' cenas. Laiks: '.$fetchedAt->toDateTimeString());

        return self::SUCCESS;
    }

    /**
     * @param  array<int, array<string, mixed>>  $stationPrices
     * @param  array<string, mixed>  $source
     */
    private function saveStationPrices(array $stationPrices, array $source, Carbon $fetchedAt): int
    {
        $savedPrices = 0;

        foreach ($stationPrices as $stationPrice) {
            $station = Station::query()->updateOrCreate(
                [
                    'brand' => $source['brand'],
                    'address' => $stationPrice['address'],
                ],
                [
                    'name' => $stationPrice['name'],
                    'brand' => $source['brand'],
                    'address' => $stationPrice['address'],
                    'latitude' => $stationPrice['latitude'],
                    'longitude' => $stationPrice['longitude'],
                ],
            );

            foreach ($stationPrice['prices'] as $fuelType => $price) {
                FuelPrice::query()->create([
                    'station_id' => $station->id,
                    'fuel_type' => $fuelType,
                    'price' => $price,
                    'source_type' => 'official_station',
                    'source_label' => 'Oficiāla stacijas cena',
                    'source_url' => $source['source_url'],
                    'fetched_at' => $fetchedAt,
                ]);

                $savedPrices++;
            }
        }

        return $savedPrices;
    }

    private function seedStations(LatvianFuelPriceSources $sources): void
    {
        collect($sources->stations())
            ->each(fn (array $stationData) => Station::query()->updateOrCreate(
                [
                    'brand' => $stationData['brand'],
                    'address' => $stationData['address'],
                ],
                $stationData,
            ));
    }

    /**
     * @param  array<string, mixed>  $source
     * @return array<int, string>
     */
    private function urlsFor(array $source): array
    {
        if (array_key_exists('_csv_url', $source['fuel_labels'] ?? [])) {
            return [$source['source_url']];
        }

        return collect([$source['source_url']])
            ->merge($source['fallback_urls'] ?? [])
            ->unique()
            ->values()
            ->all();
    }

    private function clearDebugFile(): void
    {
        $debugSource = $this->option('debug-source');

        if (! is_string($debugSource) || $debugSource === '') {
            return;
        }

        Storage::disk('local')->delete('debug/'.Str::slug($debugSource).'-fuel-source.txt');
    }

    /**
     * @param  array<string, mixed>  $source
     */
    private function priceStationsForSource(array $source)
    {
        if (($source['station_price_model'] ?? null) === 'station') {
            return Station::query()
                ->where('brand', $source['brand'])
                ->where('address', 'not like', 'Oficiālā cenu lapa:%')
                ->orderBy('name')
                ->get();
        }

        return collect([$this->publishedPriceStation($source)]);
    }

    /**
     * @param  array<string, mixed>  $source
     */
    private function publishedPriceStation(array $source): Station
    {
        return Station::query()->updateOrCreate(
            [
                'brand' => $source['brand'],
                'address' => 'Oficiālā cenu lapa: '.$source['source_url'],
            ],
            [
                'name' => $source['brand'].' publicētā cena',
                'brand' => $source['brand'],
                'address' => 'Oficiālā cenu lapa: '.$source['source_url'],
                'latitude' => 56.9496000,
                'longitude' => 24.1052000,
            ],
        );
    }

    private function pruneImportedPrices(LatvianFuelPriceSources $sources): void
    {
        $brands = collect($sources->officialSources())->pluck('brand');

        FuelPrice::query()
            ->whereHas('station', fn ($query) => $query->whereIn('brand', $brands))
            ->where('source_type', 'test_data')
            ->delete();

        FuelPrice::query()
            ->whereHas('station', fn ($query) => $query->whereIn('brand', $brands))
            ->where(function ($query): void {
                $query
                    ->where(fn ($fuelQuery) => $fuelQuery->whereIn('fuel_type', ['95', '98'])->whereNotBetween('price', [1.35, 3.00]))
                    ->orWhere(fn ($fuelQuery) => $fuelQuery->where('fuel_type', 'Diesel')->whereNotBetween('price', [1.45, 3.00]))
                    ->orWhere(fn ($fuelQuery) => $fuelQuery->where('fuel_type', 'LPG')->whereNotBetween('price', [0.45, 1.30]));
            })
            ->delete();
    }

    private function removeUnsupportedBrands(): void
    {
        $unsupportedBrands = ['KOOL'];

        FuelPrice::query()
            ->whereHas('station', fn ($query) => $query->whereIn('brand', $unsupportedBrands))
            ->delete();

        Station::query()
            ->whereIn('brand', $unsupportedBrands)
            ->delete();
    }

    private function removePublishedPriceStations(): void
    {
        FuelPrice::query()
            ->whereHas('station', fn ($query) => $query->where('address', 'like', 'Oficiālā cenu lapa:%'))
            ->delete();

        Station::query()
            ->where('address', 'like', 'Oficiālā cenu lapa:%')
            ->delete();
    }

    private function removeImpreciseStationMappedOfficialPrices(): void
    {
        FuelPrice::query()
            ->whereIn('source_type', ['official_network', 'official_lowest', 'official_published'])
            ->whereHas('station', fn ($query) => $query->where('address', 'not like', 'Oficiālā cenu lapa:%'))
            ->delete();
    }
}
