@extends('layouts.public')

@section('title', 'Latvijas degvielas cenu karte | Degvielas Cenas')
@section('description', 'Skaties jaunākos Latvijas degvielas cenu ierakstus, avotus un cenu vēsturi benzīnam, dīzelim un LPG.')
@section('canonical', route('gas.index'))

@push('head')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => 'Latvijas degvielas cenu karte',
            'description' => 'Interaktīva Latvijas degvielas cenu karte ar publiski pieejamiem cenu avotiem.',
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
