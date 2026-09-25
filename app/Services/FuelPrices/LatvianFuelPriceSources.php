<?php

namespace App\Services\FuelPrices;

class LatvianFuelPriceSources
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function officialSources(): array
    {
        return [
            [
                'brand' => 'Circle K',
                'source_type' => 'official_network',
                'source_label' => 'Oficiāla tīkla cena',
                'source_url' => 'https://www.circlek.lv/degviela-miles/degvielas-cenas',
                'station_price_model' => 'network',
                'fuel_labels' => [
                    '95' => ['95 miles', 'miles 95', '95E', '95'],
                    '98' => ['98 miles', 'miles 98', '98E', '98'],
                    'Diesel' => ['Dmiles', 'D miles', 'Dīzeļdegviela', 'Diesel'],
                    'LPG' => ['Autogāze', 'LPG'],
                ],
            ],
            [
                'brand' => 'Viada',
                'source_type' => 'official_lowest',
                'source_label' => 'Oficiāli publicēta zemākā cena',
                'source_url' => 'https://www.viada.lv/zemakas-degvielas-cenas/',
                'station_price_model' => 'published_lowest',
                'fuel_labels' => [
                    '95' => ['95E', '95'],
                    '98' => ['98E', '98'],
                    'Diesel' => ['DD', 'Dīzeļdegviela', 'Diesel'],
                    'LPG' => ['LPG', 'Autogāze'],
                    '_row_order' => ['95', '95', '98', 'Diesel', 'Diesel', 'LPG', 'Diesel'],
                ],
            ],
            [
                'brand' => 'Virsi',
                'source_type' => 'official_published',
                'source_label' => 'Oficiāli publicēta cena',
                'source_url' => 'https://www.virsi.lv/lv/privatpersonam/degviela/degvielas-un-elektrouzlades-cenas',
                'station_price_model' => 'published',
                'fuel_labels' => [
                    '95' => ['95E', '95'],
                    '98' => ['98E', '98'],
                    'Diesel' => ['DD', 'Dīzeļdegviela', 'Diesel'],
                    'LPG' => ['LPG', 'Autogāze'],
                ],
            ],
            [
                'brand' => 'Straujupīte',
                'source_type' => 'official_published',
                'source_label' => 'Oficiāli publicēta cena',
                'source_url' => 'https://straujupite.lv/degvielas-cenas/',
                'station_price_model' => 'published',
                'fuel_labels' => [
                    '95' => ['95E', 'E95', 'Petrol 95', 'Gasoline 95', 'Benzīns 95', '95'],
                    '98' => ['98E', 'E98', 'Petrol 98', 'Gasoline 98', 'Benzīns 98', '98'],
                    'Diesel' => ['Diesel', 'Dīzeļdegviela', 'Dīzelis', 'DD'],
                    'LPG' => ['LPG', 'Autogāze', 'Auto gas'],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function stations(): array
    {
        return [
            ['name' => 'Circle K Ulmanis', 'brand' => 'Circle K', 'address' => 'Karla Ulmana gatve 110, Riga', 'latitude' => 56.9236700, 'longitude' => 24.0315600],
            ['name' => 'Circle K Eksporta', 'brand' => 'Circle K', 'address' => 'Eksporta iela 1C, Riga', 'latitude' => 56.9607100, 'longitude' => 24.1012100],
            ['name' => 'Neste Teika', 'brand' => 'Neste', 'address' => 'Brivibas gatve 253, Riga', 'latitude' => 56.9821200, 'longitude' => 24.1919800],
            ['name' => 'Neste Salaspils', 'brand' => 'Neste', 'address' => 'Energetiku iela 2, Salaspils', 'latitude' => 56.8615200, 'longitude' => 24.3499300],
            ['name' => 'Virsi Teika', 'brand' => 'Virsi', 'address' => 'Brivibas gatve 297, Riga', 'latitude' => 56.9876900, 'longitude' => 24.2020800],
            ['name' => 'Virsi Valmiera', 'brand' => 'Virsi', 'address' => 'Rigas iela 81, Valmiera', 'latitude' => 57.5262600, 'longitude' => 25.4279100],
            ['name' => 'Viada Saharova', 'brand' => 'Viada', 'address' => 'Andreja Saharova iela 10, Riga', 'latitude' => 56.9440900, 'longitude' => 24.2043900],
            ['name' => 'Viada Darzciema', 'brand' => 'Viada', 'address' => 'Darzciema iela 69, Riga', 'latitude' => 56.9449000, 'longitude' => 24.1775300],
            ['name' => 'Straujupīte Rīga', 'brand' => 'Straujupīte', 'address' => 'Daugavgrivas iela 29, Riga', 'latitude' => 56.9649000, 'longitude' => 24.0580000],
        ];
    }
}
