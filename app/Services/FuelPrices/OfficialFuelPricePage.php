<?php

namespace App\Services\FuelPrices;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OfficialFuelPricePage
{
    /**
     * @param  array<string, mixed>  $source
     * @return array<int, array<string, mixed>>
     */
    public function stationPrices(string $url, array $source, ?string $debugName = null): array
    {
        $response = Http::timeout(15)
            ->accept('text/html,application/xhtml+xml')
            ->withUserAgent('Latvijas degvielas cenu karte/0.1')
            ->get($url);

        if (! $response->successful()) {
            return [];
        }

        $html = $response->body();
        $text = $this->visibleText($html);

        if ($debugName !== null) {
            $this->writeDebug($debugName, $url, $html, $text);
        }

        return match ($source['brand']) {
            'Circle K' => $this->circleKStationPrices($text),
            'Viada' => $this->viadaStationPrices($html, $source['fuel_labels']['_row_order'] ?? []),
            'Virsi' => $this->virsiStationPrices($text),
            'Straujupīte' => $this->straujupiteStationPrices($text),
            default => [],
        };
    }

    /**
     * @param  array<string, array<int, string>>  $fuelLabels
     * @return array<string, float>
     */
    public function prices(string $url, array $fuelLabels, ?string $debugName = null): array
    {
        if (array_key_exists('_csv_url', $fuelLabels)) {
            $prices = $this->pricesFromCsv($fuelLabels['_csv_url'][0], $debugName);

            if ($prices !== []) {
                return $prices;
            }
        }

        $response = Http::timeout(15)
            ->accept('text/html,application/xhtml+xml')
            ->withUserAgent('Latvijas degvielas cenu karte/0.1')
            ->get($url);

        if (! $response->successful()) {
            return [];
        }

        $html = $response->body();
        $text = $this->visibleText($html);

        if ($debugName !== null) {
            $this->writeDebug($debugName, $url, $html, $text);
        }

        $prices = $this->pricesFromFuelSectionSequence($text, $fuelLabels);

        if ($prices !== []) {
            return $prices;
        }

        $prices = $this->pricesFromOrderedRows($response->body(), $fuelLabels);

        foreach ($fuelLabels as $fuelType => $labels) {
            if (str_starts_with($fuelType, '_')) {
                continue;
            }

            if (array_key_exists($fuelType, $prices)) {
                continue;
            }

            $price = $this->firstPriceNearAnyLabel($text, $fuelType, $labels);

            if ($price !== null) {
                $prices[$fuelType] = $price;
            }
        }

        return $prices;
    }

    /**
     * @return array<string, float>
     */
    private function pricesFromCsv(string $url, ?string $debugName = null): array
    {
        $response = Http::timeout(15)
            ->accept('text/csv,text/plain,*/*')
            ->withUserAgent('Latvijas degvielas cenu karte/0.1')
            ->get($url.'&t='.time());

        if ($debugName !== null) {
            Storage::disk('local')->append(
                'debug/'.Str::slug($debugName).'-fuel-source.txt',
                "==============================\nCSV URL: {$url}\nCSV STATUS: ".$response->status()."\nCSV LENGTH: ".mb_strlen($response->body())."\n\n".$response->body()."\n",
            );
        }

        if (! $response->successful()) {
            return [];
        }

        $csv = $response->body();

        $lines = preg_split('/\r\n|\r|\n/', trim($csv)) ?: [];
        $values = [];

        foreach (array_slice($lines, 0, 6) as $line) {
            $columns = str_getcsv($line);
            $lastValue = trim((string) end($columns));

            if ($lastValue !== '') {
                $values[] = $lastValue;
            }
        }

        $rawPrices = [
            '95' => $values[1] ?? null,
            '98' => $values[2] ?? null,
            'LPG' => $values[3] ?? null,
            'Diesel' => $values[4] ?? null,
        ];

        $prices = [];

        foreach ($rawPrices as $fuelType => $rawPrice) {
            if ($rawPrice === null) {
                continue;
            }

            $price = (float) str_replace(',', '.', $rawPrice);

            if ($this->isPlausiblePrice($fuelType, $price)) {
                $prices[$fuelType] = $price;
            }
        }

        return $prices;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function circleKStationPrices(string $text): array
    {
        $fuelRows = [
            '95' => '95miles',
            '98' => '98miles\+',
            'Diesel' => 'Dmiles',
            'LPG' => 'Autogāze',
        ];

        $stations = [];

        foreach ($fuelRows as $fuelType => $label) {
            if (! preg_match('/'.$label.'\s+\|\s+([0-9]+[,.][0-9]{3})\s+EUR\s+\|\s+(.+?)(?=\s+(?:98miles\+|Dmiles\+?|miles\+|Autogāze)\s+\||\s+\*)/iu', $text, $match)) {
                continue;
            }

            $price = (float) str_replace(',', '.', $match[1]);

            if (! $this->isPlausiblePrice($fuelType, $price)) {
                continue;
            }

            foreach ($this->circleKAddresses($match[2]) as $address) {
                $this->addStationFuelPrice($stations, 'Circle K', $address, $fuelType, $price);
            }
        }

        return array_values($stations);
    }

    /**
     * @return array<int, string>
     */
    private function circleKAddresses(string $addressText): array
    {
        $knownAddresses = [
            'Jāņavārtu iela 21',
            'Lubānas iela 76A',
            'Lubānas iela 119A',
            'Eksporta iela 1C',
            'Kārļa Ulmaņa gatve 110',
            'Krasta iela 93',
            'Krišjāņa Valdemāra iela 104',
            'Dubultu prospekts 42A',
            'Anniņmuižas bulvāris 25a',
            'Kārļa Ulmaņa gatve 117',
        ];

        return collect($knownAddresses)
            ->filter(fn (string $address): bool => mb_stripos($addressText, $address) !== false)
            ->map(fn (string $address): string => $this->normalizeAddress($address))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string|null>  $rowOrder
     * @return array<int, array<string, mixed>>
     */
    private function viadaStationPrices(string $html, array $rowOrder): array
    {
        if ($rowOrder === []) {
            return [];
        }

        if (preg_match_all('/<tr\b[^>]*>(.*?)<\/tr>/is', $html, $matches) === false) {
            return [];
        }

        $stations = [];
        $dataRowIndex = 0;

        foreach ($matches[1] as $row) {
            if (! str_contains($row, 'EUR')) {
                continue;
            }

            $fuelType = $rowOrder[$dataRowIndex] ?? null;
            $dataRowIndex++;

            if ($fuelType === null || ! preg_match('/([0-9]+[,.][0-9]{3})\s*EUR/iu', $row, $priceMatch)) {
                continue;
            }

            $price = (float) str_replace(',', '.', $priceMatch[1]);

            if (! $this->isPlausiblePrice($fuelType, $price)) {
                continue;
            }

            foreach ($this->viadaStationsFromText($this->visibleText($row)) as $station) {
                $this->addStationFuelPrice($stations, 'Viada', $station['address'], $fuelType, $price, $station['name']);
            }
        }

        return array_values($stations);
    }

    /**
     * @return array<int, array{name: string, address: string}>
     */
    private function viadaStationsFromText(string $rowText): array
    {
        preg_match_all('/(?:A?DUS)\s+([^:]+):\s+(.+?)(?=,\s+(?:A?DUS)\s+[^:]+:|\.?$)/u', $rowText, $matches, PREG_SET_ORDER);

        return collect($matches)
            ->map(fn (array $match): array => [
                'name' => 'Viada '.trim($match[1]),
                'address' => $this->normalizeAddress(trim($match[2])),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function virsiStationPrices(string $text): array
    {
        $stations = [];
        $fuelLabels = [
            'DD' => 'Diesel',
            '95E' => '95',
            '98E' => '98',
            'LPG' => 'LPG',
        ];

        foreach ($fuelLabels as $label => $fuelType) {
            if (! preg_match('/'.$label.'\s+([0-9]+[,.][0-9]{3})\s+(.+?)(?=\s+(?:AD|95E|98E|CNG|LPG|AdBLUE|##))/iu', $text, $match)) {
                continue;
            }

            $address = trim($match[2]);

            if (str_contains($address, 'Visā Viršu tīklā')) {
                continue;
            }

            $price = (float) str_replace(',', '.', $match[1]);

            if ($this->isPlausiblePrice($fuelType, $price)) {
                $this->addStationFuelPrice($stations, 'Virsi', $this->normalizeAddress($address), $fuelType, $price, 'Virši Brīvības');
            }
        }

        return array_values($stations);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function straujupiteStationPrices(string $text): array
    {
        $stations = [];

        preg_match_all('/(Benzīns 95|Dīzeļdegviela).*?Degvielas cena\s+([0-9]+[,.][0-9]{3})\s*€\s+Adrese\s+(.+?)(?=\s+(?:Benzīns 95|Dīzeļdegviela|Atjaunots:))/iu', $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $fuelType = str_contains($match[1], 'Benzīns') ? '95' : 'Diesel';
            $price = (float) str_replace(',', '.', $match[2]);
            $address = $this->normalizeAddress($match[3]);

            if ($this->isPlausiblePrice($fuelType, $price)) {
                $this->addStationFuelPrice($stations, 'Straujupīte', $address, $fuelType, $price, 'Straujupīte Ainaži');
            }
        }

        return array_values($stations);
    }

    /**
     * @param  array<string, array<string, mixed>>  $stations
     */
    private function addStationFuelPrice(array &$stations, string $brand, string $address, string $fuelType, float $price, ?string $name = null): void
    {
        $coordinates = $this->coordinatesFor($address, $brand);

        if ($coordinates === null) {
            return;
        }

        $key = $brand.'|'.$address;

        if (! isset($stations[$key])) {
            $stations[$key] = [
                'name' => $name ?? $brand.' '.$this->stationNameFromAddress($address),
                'address' => $address,
                'latitude' => $coordinates[0],
                'longitude' => $coordinates[1],
                'prices' => [],
            ];
        }

        $stations[$key]['prices'][$fuelType] = $price;
    }

    private function normalizeAddress(string $address): string
    {
        $address = trim(preg_replace('/\s+/u', ' ', $address) ?? $address);
        $address = str_replace(['Kārļa Umaņa gatve'], ['Kārļa Ulmaņa gatve'], $address);
        $address = preg_replace('/,\s*LV-\d{4}/u', '', $address) ?? $address;

        if (str_contains($address, 'Dubultu prospekts') && ! str_contains($address, 'Jūrmala')) {
            return $address.', Jūrmala';
        }

        if (! preg_match('/,\s*(Rīga|Liepāja|Jūrmala|Ainaži|Salacgrīvas nov\.)/u', $address)) {
            return $address.', Rīga';
        }

        return $address;
    }

    private function stationNameFromAddress(string $address): string
    {
        return trim(str($address)->before(',')->toString());
    }

    /**
     * @return array{0: float, 1: float}|null
     */
    /**
     * @return array{0: float, 1: float}|null
     */
    private function coordinatesFor(string $address, string $brand): ?array
    {
        $cacheKey = 'fuel_geocode_v2:'.sha1($brand.'|'.$address);

        $coordinates = Cache::store('file')->rememberForever($cacheKey, function () use ($address, $brand): ?array {
            return $this->geocodeStation($brand, $address);
        });

        return $coordinates ?? $this->fallbackCoordinatesFor($address);
    }

    /**
     * @return array{0: float, 1: float}|null
     */
    private function geocodeStation(string $brand, string $address): ?array
    {
        foreach ($this->geocodeQueries($brand, $address) as $query) {
            $coordinates = $this->geocodeQuery($query);

            if ($coordinates !== null) {
                return $coordinates;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function geocodeQueries(string $brand, string $address): array
    {
        $brandQuery = match ($brand) {
            'Virsi' => 'Virši',
            default => $brand,
        };

        return [
            "{$brandQuery} {$address}",
            "{$brandQuery}, {$address}",
            "{$address}, Latvija",
        ];
    }

    /**
     * @return array{0: float, 1: float}|null
     */
    private function geocodeQuery(string $query): ?array
    {
        static $lastRequestAt = 0.0;

        $elapsed = microtime(true) - $lastRequestAt;

        if ($lastRequestAt > 0 && $elapsed < 1.1) {
            usleep((int) ((1.1 - $elapsed) * 1_000_000));
        }

        $lastRequestAt = microtime(true);

        $response = Http::timeout(15)
            ->acceptJson()
            ->withUserAgent('Latvijas degvielas cenu karte/0.1 (local development geocoder)')
            ->get('https://nominatim.openstreetmap.org/search', [
                'q' => $query,
                'format' => 'jsonv2',
                'limit' => 1,
                'countrycodes' => 'lv',
            ]);

        if (! $response->successful()) {
            return null;
        }

        $result = $response->json('0');

        if (! is_array($result) || ! isset($result['lat'], $result['lon'])) {
            return null;
        }

        return [(float) $result['lat'], (float) $result['lon']];
    }

    /**
     * @return array{0: float, 1: float}|null
     */
    private function fallbackCoordinatesFor(string $address): ?array
    {
        $coordinates = [
            'Andreja Saharova iela 10, Rīga' => [56.9348690, 24.2029580],
            'Zemnieku iela 58, Liepāja' => [56.5309000, 21.0462000],
            'Vecā Biķernieku iela 1, Rīga' => [56.9583512, 24.2393170],
            'Kārļa Ulmaņa gatve 67, Rīga' => [56.9295000, 24.0650000],
            'Valgales iela 1, Rīga' => [56.9305000, 24.0714000],
            'Salaspils iela 4a, Rīga' => [56.9140000, 24.1789000],
            'Valdeķu iela 34, Rīga' => [56.9103230, 24.0961730],
            'Dārzciema iela 69, Rīga' => [56.9449000, 24.1775300],
            'Dārzciema iela 62, Rīga' => [56.9444000, 24.1757000],
            'G.Astras iela 7, Rīga' => [56.9582000, 24.1931000],
            'Emmas iela 45, Rīga' => [57.0323000, 24.1068000],
            'Jāņavārtu iela 21, Rīga' => [56.9218000, 24.1745000],
            'Lubānas iela 76A, Rīga' => [56.9374000, 24.1996000],
            'Lubānas iela 119A, Rīga' => [56.9345000, 24.2313000],
            'Eksporta iela 1C, Rīga' => [56.9607100, 24.1012100],
            'Kārļa Ulmaņa gatve 110, Rīga' => [56.9236700, 24.0315600],
            'Krasta iela 93, Rīga' => [56.9287000, 24.1636000],
            'Krišjāņa Valdemāra iela 104, Rīga' => [56.9704000, 24.1320000],
            'Dubultu prospekts 42A, Jūrmala' => [56.9696000, 23.7744000],
            'Anniņmuižas bulvāris 25a, Rīga' => [56.9543000, 24.0001000],
            'Kārļa Ulmaņa gatve 117, Rīga' => [56.9204000, 24.0345000],
            'Brīvības gatve 297, Rīga' => [56.9876900, 24.2020800],
            'Ainaži, Salacgrīvas nov.' => [57.8639000, 24.3588000],
        ];

        return $coordinates[$address] ?? null;
    }

    private function writeDebug(string $debugName, string $url, string $html, string $text): void
    {
        Storage::disk('local')->append(
            'debug/'.Str::slug($debugName).'-fuel-source.txt',
            "==============================\nURL: {$url}\nTEXT LENGTH: ".mb_strlen($text)."\nHTML LENGTH: ".mb_strlen($html)."\n\n".$text."\n",
        );

        Storage::disk('local')->append(
            'debug/'.Str::slug($debugName).'-fuel-source.raw.html',
            "\n\n<!-- ============================== -->\n<!-- URL: {$url} -->\n<!-- HTML LENGTH: ".mb_strlen($html)." -->\n\n".$html,
        );
    }

    /**
     * @param  array<string, array<int, string>>  $fuelLabels
     * @return array<string, float>
     */
    private function pricesFromFuelSectionSequence(string $text, array $fuelLabels): array
    {
        if (! array_key_exists('_fuel_section_order', $fuelLabels)) {
            return [];
        }

        $start = mb_stripos($text, 'Zemākās cenas DUS tīklā');

        if ($start === false) {
            return [];
        }

        $section = mb_substr($text, $start, 900);

        if (mb_stripos($section, '95E') === false || mb_stripos($section, 'DD') === false) {
            return [];
        }

        preg_match_all('/(?<!\d)([0-9]+[,.][0-9]{3})(?!\d)/u', $section, $matches);

        $candidates = collect($matches[1] ?? [])
            ->map(fn (string $match): float => (float) str_replace(',', '.', $match))
            ->filter(fn (float $price): bool => $price >= 1.35 && $price <= 3.00)
            ->values()
            ->all();

        return $this->pricesFromSectionCandidates($candidates, $fuelLabels['_fuel_section_order']);
    }

    /**
     * @param  array<int, float>  $candidates
     * @param  array<string, int>  $positions
     * @return array<string, float>
     */
    private function pricesFromSectionCandidates(array $candidates, array $positions): array
    {
        $prices = [];

        foreach ($positions as $fuelType => $positionFromEnd) {
            $index = count($candidates) - $positionFromEnd;

            if (! array_key_exists($index, $candidates)) {
                continue;
            }

            $price = $candidates[$index];

            if ($this->isPlausiblePrice($fuelType, $price)) {
                $prices[$fuelType] = $price;
            }
        }

        return $prices;
    }

    /**
     * @param  array<string, array<int, string>>  $fuelLabels
     * @return array<string, float>
     */
    private function pricesFromOrderedRows(string $html, array $fuelLabels): array
    {
        if (! array_key_exists('_row_order', $fuelLabels)) {
            return [];
        }

        $rowOrder = $fuelLabels['_row_order'];
        $rows = [];

        if (preg_match_all('/<tr\b[^>]*>(.*?)<\/tr>/is', $html, $matches) !== false) {
            $rows = $matches[1];
        }

        if ($rows === []) {
            $text = $this->visibleText($html);
            preg_match_all('/([0-9]+[,.][0-9]{3})\s*EUR/iu', $text, $priceMatches);

            return $this->pricesFromSequence($priceMatches[1] ?? [], $rowOrder);
        }

        $prices = [];
        $dataRowIndex = 0;

        foreach ($rows as $row) {
            if (! str_contains($row, 'EUR')) {
                continue;
            }

            $fuelType = $rowOrder[$dataRowIndex] ?? null;
            $dataRowIndex++;

            if ($fuelType === null || ! preg_match('/([0-9]+[,.][0-9]{3})\s*EUR/iu', $row, $priceMatch)) {
                continue;
            }

            $price = (float) str_replace(',', '.', $priceMatch[1]);

            if (! $this->isPlausiblePrice($fuelType, $price)) {
                continue;
            }

            if (! isset($prices[$fuelType]) || $price < $prices[$fuelType]) {
                $prices[$fuelType] = $price;
            }
        }

        return $prices;
    }

    /**
     * @param  array<int, string>  $priceMatches
     * @param  array<int, string|null>  $rowOrder
     * @return array<string, float>
     */
    private function pricesFromSequence(array $priceMatches, array $rowOrder): array
    {
        $prices = [];

        foreach ($priceMatches as $index => $match) {
            $fuelType = $rowOrder[$index] ?? null;

            if ($fuelType === null) {
                continue;
            }

            $price = (float) str_replace(',', '.', $match);

            if (! $this->isPlausiblePrice($fuelType, $price)) {
                continue;
            }

            if (! isset($prices[$fuelType]) || $price < $prices[$fuelType]) {
                $prices[$fuelType] = $price;
            }
        }

        return $prices;
    }

    private function visibleText(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', ' ', $html) ?? $html;
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', ' ', $html) ?? $html;
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return Str::of($text)
            ->replaceMatches('/\s+/u', ' ')
            ->trim()
            ->toString();
    }

    /**
     * @param  array<int, string>  $labels
     */
    private function firstPriceNearAnyLabel(string $text, string $fuelType, array $labels): ?float
    {
        foreach ($labels as $label) {
            $label = preg_quote($label, '/');
            $patterns = [
                '/'.$label.'.{0,140}?([0-9]+[,.][0-9]{3})/iu',
                '/([0-9]+[,.][0-9]{3}).{0,140}?'.$label.'/iu',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match_all($pattern, $text, $matches) === false) {
                    continue;
                }

                foreach ($matches[1] as $match) {
                    $price = (float) str_replace(',', '.', $match);

                    if ($this->isPlausiblePrice($fuelType, $price)) {
                        return $price;
                    }
                }
            }
        }

        return null;
    }

    private function isPlausiblePrice(string $fuelType, float $price): bool
    {
        return match ($fuelType) {
            '95', '98' => $price >= 1.35 && $price <= 3.00,
            'Diesel' => $price >= 1.45 && $price <= 3.00,
            'LPG' => $price >= 0.45 && $price <= 1.30,
            default => false,
        };
    }
}
