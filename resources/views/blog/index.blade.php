@extends('layouts.public')

@section('title', 'Blogs par degvielas cenām Latvijā | Degvielas Cenas')
@section('description', 'Praktiski raksti par degvielas cenām Latvijā, cenu salīdzināšanu, brauciena izmaksām un publisko cenu avotu uzticamību.')
@section('canonical', route('blog.index'))

@push('head')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Blog',
            'name' => 'Degvielas Cenas Latvijā blogs',
            'description' => 'Raksti par degvielas cenu salīdzināšanu Latvijā.',
            'url' => route('blog.index'),
            'blogPost' => $posts->map(fn (array $post): array => [
                '@type' => 'BlogPosting',
                'headline' => $post['title'],
                'description' => $post['description'],
                'url' => route('blog.show', $post['slug']),
                'datePublished' => $post['published_at'],
                'dateModified' => $post['updated_at'],
            ])->values(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
@endpush

@section('content')
    <main class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <section class="max-w-3xl">
            <h1 class="text-3xl font-semibold tracking-tight text-white sm:text-5xl">Blogs par degvielas cenām Latvijā</h1>
            <p class="mt-4 text-base leading-7 text-cyan-100/80">
                Praktiski skaidrojumi autovadītājiem: kā salīdzināt cenas, kad lētākā stacija nav izdevīgākā, kā atšķirt oficiālu stacijas cenu no tīkla cenas un kā veidot cenu vēsturi bez izdomātiem datiem.
            </p>
        </section>

        <section class="mt-10 grid gap-4 md:grid-cols-2">
            @foreach ($posts as $post)
                <article class="rounded-2xl border border-white/10 bg-white/[0.04] p-5 shadow-2xl shadow-black/20 transition hover:border-cyan-300/40 hover:bg-white/[0.06]">
                    <div class="flex items-center gap-2">
                        <span class="rounded-full border border-emerald-300/30 bg-emerald-300/10 px-2.5 py-1 text-xs font-semibold text-emerald-100">{{ $post['tag'] }}</span>
                        <span class="text-xs text-slate-400">{{ $post['minutes'] }}</span>
                    </div>
                    <h2 class="mt-4 text-xl font-semibold leading-7 text-white">
                        <a class="transition hover:text-cyan-100" href="{{ route('blog.show', $post['slug']) }}">{{ $post['title'] }}</a>
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-slate-300">{{ $post['description'] }}</p>
                    <a href="{{ route('blog.show', $post['slug']) }}" class="mt-5 inline-flex text-sm font-semibold text-cyan-200 transition hover:text-cyan-100">
                        Lasīt rakstu
                    </a>
                </article>
            @endforeach
        </section>
    </main>
@endsection
