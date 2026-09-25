@extends('layouts.public')

@section('title', 'Degvielas atlaides Latvijā | Circle K, Neste, Virši, Viada')
@section('description', 'Salīdzini degvielas atlaižu kartes un akcijas Latvijā ar konkrētu atlaidi centos litrā Circle K, Neste, Virši, Viada un citos DUS.')
@section('canonical', route('discounts.index'))

@php
    $tones = [
        'Circle K' => 'border-red-400/30 bg-red-400/10 text-red-100',
        'Neste' => 'border-blue-400/30 bg-blue-400/10 text-blue-100',
        'Virši' => 'border-emerald-400/30 bg-emerald-400/10 text-emerald-100',
        'Viada' => 'border-yellow-300/30 bg-yellow-300/10 text-yellow-100',
        'ASTARTE' => 'border-orange-300/30 bg-orange-300/10 text-orange-100',
    ];
@endphp

@push('head')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Degvielas atlaides un akcijas Latvijā',
            'description' => 'Degvielas atlaižu un lojalitātes piedāvājumu saraksts ar konkrētām centi/l atlaidēm.',
            'url' => route('discounts.index'),
            'inLanguage' => 'lv-LV',
            'mainEntity' => $offers->map(fn (array $offer): array => [
                '@type' => 'Offer',
                'name' => $offer['brand'].' - '.$offer['name'],
                'description' => $offer['discount'].'; '.$offer['details'],
                'url' => $offer['source_url'],
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $offer['brand'],
                ],
            ])->values(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
@endpush

@section('content')
    <main class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <section class="max-w-3xl">
            <h1 class="text-3xl font-semibold tracking-tight text-white sm:text-5xl">Degvielas atlaides un akcijas Latvijā</h1>
            <p class="mt-4 text-base leading-7 text-cyan-100/80">
                Šeit ir tikai tie piedāvājumi, kur publiski norādīta konkrēta atlaide centos par litru vai skaidrs cents/l ieguvums. Piedāvājumi bez konkrēta cipara nav iekļauti.
            </p>
        </section>

        <section class="mt-10 rounded-2xl border border-emerald-300/20 bg-gray-950/70 p-4 shadow-2xl shadow-black/25">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">Pārbaudāmas atlaides</h2>
                    <p class="mt-1 max-w-3xl text-sm leading-6 text-cyan-100/75">
                        Salīdzini lojalitātes kartes, lietotņu kuponus un ģimenes kartes piedāvājumus, kur atlaide ir izmērāma.
                    </p>
                </div>
                <span class="rounded-full border border-emerald-300/30 bg-emerald-300/10 px-3 py-1 text-xs font-semibold text-emerald-100">
                    {{ $offers->count() }} piedāvājumi
                </span>
            </div>

            <div class="mt-5 overflow-x-auto rounded-xl border border-white/10">
                <table class="min-w-[48rem] w-full border-collapse text-left text-sm">
                    <thead class="bg-white/[0.04] text-xs uppercase text-cyan-100/75">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Tīkls</th>
                            <th class="px-4 py-3 font-semibold">Piedāvājums</th>
                            <th class="px-4 py-3 font-semibold">Atlaide</th>
                            <th class="px-4 py-3 font-semibold">Nosacījums</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach ($offers as $offer)
                            <tr class="bg-white/[0.02] transition hover:bg-white/[0.05]">
                                <td class="px-4 py-4 align-top">
                                    <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $tones[$offer['brand']] ?? 'border-cyan-300/30 bg-cyan-300/10 text-cyan-100' }}">
                                        {{ $offer['brand'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <p class="font-semibold text-white">{{ $offer['name'] }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ $offer['applies_to'] }}</p>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <span class="text-xl font-semibold tabular-nums text-emerald-200">{{ $offer['discount'] }}</span>
                                </td>
                                <td class="max-w-sm px-4 py-4 align-top text-slate-300">
                                    {{ $offer['details'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="mt-3 text-xs leading-5 text-slate-400">
                Atlaides var nesummēties ar citām akcijām un var mainīties. Pirms uzpildes pārbaudi nosacījumus attiecīgā DUS tīkla lietotnē vai oficiālajā lapā.
            </p>
        </section>
    </main>
@endsection
