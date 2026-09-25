@extends('layouts.public')

@section('title', 'Par degvielas cenu projektu Latvijā | Datu avoti un uzticamība')
@section('description', 'Uzzini, kā Degvielas Cenas Latvijā apstrādā publiskos DUS cenu avotus, cenu vēsturi, lietotāju ziņojumus un datu uzticamību.')
@section('canonical', route('about'))

@push('head')
    <script type="application/ld+json">
        {!! json_encode([
            chr(64).'context' => 'https://schema.org',
            '@type' => 'AboutPage',
            'name' => 'Par Degvielas Cenas Latvijā projektu',
            'description' => 'Informācija par degvielas cenu datu principiem, avotiem un ierobežojumiem Latvijā.',
            'url' => route('about'),
            'inLanguage' => 'lv-LV',
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'Degvielas Cenas Latvijā',
                'url' => route('gas.index'),
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
@endpush

@section('content')
    <main class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <section class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-start">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-white sm:text-5xl">Par Degvielas Cenas projektu</h1>
                <p class="mt-4 max-w-3xl text-base leading-7 text-cyan-100/80">
                    Mērķis ir izveidot skaidru un uzticamu rīku Latvijas autovadītājiem: vienā vietā redzēt cenu ierakstus, avotus, cenu vēsturi un vēlāk arī aprēķinu, vai ir vērts braukt līdz tālākai DUS.
                </p>
            </div>

            <aside class="rounded-2xl border border-emerald-300/20 bg-emerald-300/10 p-5">
                <h2 class="text-lg font-semibold text-emerald-100">Galvenais princips</h2>
                <p class="mt-3 text-sm leading-6 text-slate-200">
                    Ja cenu nevar droši nolasīt vai pārbaudīt, tā netiek pasniegta kā precīza oficiāla cena.
                </p>
            </aside>
        </section>

        <section class="mt-10 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                <h2 class="text-lg font-semibold text-white">Publiskie avoti</h2>
                <p class="mt-3 text-sm leading-6 text-slate-300">
                    Kur iespējams, cena tiek iegūta no oficiālām DUS tīklu lapām. Ja lapa rāda tikai zemāko cenu, tas tiek skaidri norādīts pie avota.
                </p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                <h2 class="text-lg font-semibold text-white">Cenu vēsture</h2>
                <p class="mt-3 text-sm leading-6 text-slate-300">
                    Katrs savāktais cenu ieraksts tiek saglabāts ar laiku. Tas ļauj veidot diagrammas no datiem, ko aplikācija pati ir savākusi.
                </p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                <h2 class="text-lg font-semibold text-white">Lietotāju ziņojumi</h2>
                <p class="mt-3 text-sm leading-6 text-slate-300">
                    Tīkliem, kas nepublicē aktuālās cenas tiešsaistē, piemēram Neste, nākotnē vajadzīgs lietotāju ziņojumu vai partnera datu risinājums.
                </p>
            </div>
        </section>

        <section class="mt-10 rounded-2xl border border-white/10 bg-gray-950/70 p-6">
            <h2 class="text-xl font-semibold text-white">Ko projekts nedara</h2>
            <div class="mt-4 grid gap-3 text-sm leading-6 text-slate-300 md:grid-cols-2">
                <p>Neuzdod testa datus par reālām cenām.</p>
                <p>Nepieliek tīkla zemāko cenu katrai konkrētai stacijai, ja tam nav droša pamata.</p>
                <p>Nesola pilnu Neste tiešsaistes cenu pārklājumu bez oficiālas plūsmas vai lietotāju ziņojumiem.</p>
                <p>Nekopē trešo pušu cenu salīdzināšanas portālus bez atļaujas.</p>
            </div>
        </section>
    </main>
@endsection
