@extends('layouts.public')

@section('title', ($post['seo_title'] ?? $post['title'].' | Degvielas Cenas'))
@section('description', $post['description'])
@section('canonical', route('blog.show', $post['slug']))
@section('og_type', 'article')

@push('head')
    <meta property="article:published_time" content="{{ $post['published_at'] }}">
    <meta property="article:modified_time" content="{{ $post['updated_at'] }}">
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post['title'],
            'description' => $post['description'],
            'datePublished' => $post['published_at'],
            'dateModified' => $post['updated_at'],
            'inLanguage' => 'lv-LV',
            'url' => route('blog.show', $post['slug']),
            'mainEntityOfPage' => route('blog.show', $post['slug']),
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Degvielas Cenas Latvijā',
                'url' => route('gas.index'),
            ],
            'author' => [
                '@type' => 'Organization',
                'name' => 'Degvielas Cenas Latvijā',
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($post['faq'])->map(fn (array $faq): array => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ])->values(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
@endpush

@section('content')
    <main class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <article class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
            <div class="max-w-3xl">
                <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-cyan-200 transition hover:text-cyan-100">Atpakaļ uz blogu</a>
                <div class="mt-5 flex flex-wrap items-center gap-2">
                    <span class="rounded-full border border-emerald-300/30 bg-emerald-300/10 px-2.5 py-1 text-xs font-semibold text-emerald-100">{{ $post['tag'] }}</span>
                    <span class="text-xs text-slate-400">{{ $post['minutes'] }}</span>
                    <span class="text-xs text-slate-400">Atjaunots {{ \Illuminate\Support\Carbon::parse($post['updated_at'])->format('d.m.Y') }}</span>
                </div>

                <h1 class="mt-5 text-3xl font-semibold tracking-tight text-white sm:text-5xl">{{ $post['title'] }}</h1>
                <p class="mt-5 text-lg leading-8 text-cyan-100/85">{{ $post['intro'] }}</p>

                <div class="mt-10 grid gap-8">
                    @foreach ($post['sections'] as $section)
                        <section>
                            <h2 class="text-2xl font-semibold tracking-tight text-white">{{ $section['heading'] }}</h2>
                            <div class="mt-4 grid gap-4 text-base leading-7 text-slate-300">
                                @foreach ($section['body'] as $paragraph)
                                    <p>{{ $paragraph }}</p>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>

                <section class="mt-10 rounded-2xl border border-cyan-300/20 bg-cyan-950/20 p-6">
                    <h2 class="text-2xl font-semibold text-white">Biežākie jautājumi</h2>
                    <div class="mt-5 grid gap-4">
                        @foreach ($post['faq'] as $faq)
                            <div class="rounded-xl border border-white/10 bg-white/[0.04] p-4">
                                <h3 class="font-semibold text-cyan-100">{{ $faq['question'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-300">{{ $faq['answer'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <aside class="rounded-2xl border border-white/10 bg-gray-950/70 p-5 lg:sticky lg:top-28">
                <h2 class="text-sm font-semibold uppercase text-cyan-100/80">Saistītie raksti</h2>
                <div class="mt-4 grid gap-3">
                    @foreach ($posts->where('slug', '!=', $post['slug'])->take(3) as $related)
                        <a href="{{ route('blog.show', $related['slug']) }}" class="rounded-xl border border-white/10 bg-white/[0.04] p-3 text-sm font-semibold leading-5 text-slate-200 transition hover:border-cyan-300/40 hover:text-white">
                            {{ $related['title'] }}
                        </a>
                    @endforeach
                </div>
            </aside>
        </article>
    </main>
@endsection
