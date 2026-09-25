<!DOCTYPE html>
<html lang="lv">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Degvielas cenas Latvijā šodien | Lētākās DUS kartē')</title>
        <meta name="description" content="@yield('description', 'Salīdzini aktuālos Latvijas degvielas cenu ierakstus kartē un atrodi lētāko 95, 98, dīzeļa vai LPG cenu tuvākajās DUS.')">
        <meta name="robots" content="index, follow">
        <meta name="application-name" content="Degvielas Cenas Latvijā">
        <meta name="theme-color" content="#030712">
        <link rel="canonical" href="@yield('canonical', url()->current())">
        <meta property="og:title" content="@yield('title', 'Degvielas cenas Latvijā šodien | Lētākās DUS kartē')">
        <meta property="og:description" content="@yield('description', 'Salīdzini aktuālos Latvijas degvielas cenu ierakstus kartē un atrodi lētāko 95, 98, dīzeļa vai LPG cenu tuvākajās DUS.')">
        <meta property="og:type" content="@yield('og_type', 'website')">
        <meta property="og:url" content="@yield('canonical', url()->current())">
        <meta property="og:site_name" content="Degvielas Cenas Latvijā">
        <meta property="og:locale" content="lv_LV">
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="@yield('title', 'Degvielas cenas Latvijā šodien | Lētākās DUS kartē')">
        <meta name="twitter:description" content="@yield('description', 'Salīdzini aktuālos Latvijas degvielas cenu ierakstus kartē un atrodi lētāko 95, 98, dīzeļa vai LPG cenu tuvākajās DUS.')">
        <link rel="sitemap" type="application/xml" href="{{ route('seo.sitemap') }}">
        <link rel="alternate" type="text/plain" href="{{ route('seo.llms') }}" title="LLMs.txt">
        @stack('head')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gray-950 text-slate-100 antialiased">
        <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(20,184,166,0.18),_transparent_32rem),#030712]">
            @include('partials.public-header')

            @yield('content')

            @include('partials.public-footer')
        </div>
    </body>
</html>
