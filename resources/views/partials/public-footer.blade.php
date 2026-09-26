<footer class="border-t border-white/10 bg-gray-950/88">
    <div class="mx-auto grid w-full max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1.2fr_0.8fr_0.8fr] lg:px-8">
        <div>
            <h2 class="text-lg font-semibold text-white">Degvielas Cenas Latvijā</h2>
            <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300">
                Projekts, kas salīdzina publiski pieejamus cenu ierakstus un skaidri norāda avotu. Mūsu mērķis ir palīdzēt tev ietaupīt, nodrošinot caurskatāmus datus. Ja cena nav droša, mēs rādām tukšu vietu, nevis maldinošu ciparu.
            </p>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-cyan-100">Sadaļas</h3>
            <div class="mt-3 grid gap-2 text-sm">
                <a class="text-slate-300 transition hover:text-white" href="{{ route('gas.index') }}">Cenu karte</a>
                <a class="text-slate-300 transition hover:text-white" href="{{ route('discounts.index') }}">Degvielas atlaides</a>
                <a class="text-slate-300 transition hover:text-white" href="{{ route('blog.index') }}">Blogs</a>
                <a class="text-slate-300 transition hover:text-white" href="{{ route('about') }}">Par projektu</a>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-cyan-100">Datu statuss</h3>
            <p class="mt-3 text-sm leading-6 text-slate-300">
                Informācija tiek apkopota no publiskiem avotiem. Cenas uzpildes stacijās var mainīties, tāpēc vienmēr pārliecinies par cenu pirms uzpildes.
            </p>
        </div>
    </div>
</footer>
