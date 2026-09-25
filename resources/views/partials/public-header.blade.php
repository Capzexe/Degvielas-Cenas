@php
    $links = [
        ['label' => 'Cenu karte', 'route' => 'gas.index'],
        ['label' => 'Akcijas', 'route' => 'discounts.index'],
        ['label' => 'Blogs', 'route' => 'blog.index'],
        ['label' => 'Par projektu', 'route' => 'about'],
    ];
@endphp

<header class="sticky top-0 z-[2000] border-b border-white/10 bg-gray-950/88 backdrop-blur">
    <nav class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8" aria-label="Galvenā navigācija">
        <a href="{{ route('gas.index') }}" class="group flex items-center gap-3">
            <span class="flex size-10 items-center justify-center rounded-xl border border-emerald-300/30 bg-emerald-300/10 text-sm font-black text-emerald-200">
                DC
            </span>
            <span class="min-w-0">
                <span class="block text-sm font-semibold text-white">Degvielas Cenas</span>
                <span class="block text-xs text-cyan-100/70">Latvija</span>
            </span>
        </a>

        <div class="hidden items-center gap-1 rounded-xl border border-white/10 bg-white/[0.03] p-1 md:flex">
            @foreach ($links as $link)
                <a
                    href="{{ route($link['route']) }}"
                    class="rounded-lg px-3 py-2 text-sm font-semibold transition {{ request()->routeIs($link['route']) ? 'bg-cyan-300 text-gray-950' : 'text-slate-300 hover:bg-white/[0.08] hover:text-white' }}"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>

        <a href="{{ route('gas.index') }}" class="rounded-xl bg-emerald-300 px-4 py-2 text-sm font-bold text-gray-950 shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-200">
            Skatīt cenas
        </a>
    </nav>

    <div class="mx-auto flex w-full max-w-7xl gap-2 overflow-x-auto px-4 pb-4 sm:px-6 md:hidden lg:px-8">
        @foreach ($links as $link)
            <a
                href="{{ route($link['route']) }}"
                class="shrink-0 rounded-lg border px-3 py-2 text-sm font-semibold transition {{ request()->routeIs($link['route']) ? 'border-cyan-300 bg-cyan-300 text-gray-950' : 'border-white/10 bg-white/[0.03] text-slate-300 hover:text-white' }}"
            >
                {{ $link['label'] }}
            </a>
        @endforeach
    </div>
</header>
