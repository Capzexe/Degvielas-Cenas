@extends('layouts.public')

@section('title', 'Degvielas cenas Latvijā šodien | Lētākās DUS kartē')
@section('description', 'Salīdzini aktuālās degvielas cenas Latvijā kartē: 95, 98, dīzelis un LPG no publiski pārbaudāmiem DUS cenu avotiem.')
@section('canonical', route('gas.index'))

@push('head')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => 'Degvielas cenas Latvijā šodien',
            'description' => 'Interaktīva Latvijas degvielas cenu karte ar publiski pieejamiem DUS cenu avotiem.',
            'url' => route('gas.index'),
            'applicationCategory' => 'MapApplication',
            'operatingSystem' => 'Web',
            'inLanguage' => 'lv-LV',
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Degvielas Cenas Latvijā',
                'url' => route('gas.index'),
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
@endpush

@section('content')
    <div id="app"></div>
@endsection
