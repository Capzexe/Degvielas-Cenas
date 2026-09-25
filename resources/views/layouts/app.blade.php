<!doctype html>
<html lang="lv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'AutoServiss' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f4f6f8; color: #17202a; font-family: Arial, Helvetica, sans-serif; }
        main { width: min(1080px, calc(100% - 32px)); margin: 32px auto; }
        .topbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 24px; }
        .brand { color: #111827; font-size: 24px; font-weight: 800; text-decoration: none; }
        .nav { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        h1 { margin: 0 0 6px; font-size: 30px; }
        h2 { margin: 0 0 14px; font-size: 20px; }
        p { line-height: 1.5; }
        a { color: #1d4ed8; }
        .muted { color: #64748b; }
        .button, button {
            display: inline-flex; align-items: center; justify-content: center; min-height: 40px;
            padding: 0 14px; border: 0; border-radius: 6px; background: #1d4ed8;
            color: #fff; font: inherit; text-decoration: none; cursor: pointer;
        }
        .button.secondary, button.secondary { background: #e2e8f0; color: #0f172a; }
        .button.danger, button.danger { background: #b91c1c; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; }
        .dashboard-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 18px; margin-bottom: 20px; }
        .dashboard-head p { max-width: 68ch; margin: 0; }
        .metric-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin-bottom: 18px; }
        .metric { background: #fff; border: 1px solid #dbe3ea; border-radius: 8px; padding: 16px; }
        .metric strong { display: block; margin-bottom: 4px; font-size: 28px; line-height: 1; }
        .metric span { color: #475569; font-size: 14px; }
        .panel { background: #fff; border: 1px solid #dbe3ea; border-radius: 8px; padding: 18px; }
        .section-title { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 8px; }
        .section-title p { max-width: 62ch; margin: 0; }
        .empty-state { display: grid; gap: 10px; padding: 8px 0; }
        .row { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 14px 0; border-top: 1px solid #e5eaf0; }
        .row:first-child { border-top: 0; padding-top: 0; }
        .stack { display: grid; gap: 14px; }
        label { display: grid; gap: 7px; font-weight: 700; }
        input, select, textarea { width: 100%; min-height: 42px; padding: 9px 11px; border: 1px solid #cbd5e1; border-radius: 6px; font: inherit; background: #fff; }
        textarea { min-height: 96px; resize: vertical; }
        .status { margin-bottom: 16px; padding: 12px 14px; border-radius: 6px; background: #dcfce7; color: #166534; }
        .error { color: #b91c1c; font-size: 14px; font-weight: 400; }
        .badge { display: inline-flex; align-items: center; min-height: 26px; padding: 0 9px; border-radius: 999px; background: #e0f2fe; color: #075985; font-size: 13px; font-weight: 700; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        @media (max-width: 640px) {
            .dashboard-head, .section-title { flex-direction: column; }
            .topbar { align-items: flex-start; flex-direction: column; }
            .row { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>
<body>
<main>
    <div class="topbar">
        <a class="brand" href="{{ route('appointments.index') }}">AutoServiss</a>
        <nav class="nav">
            @auth
                <a href="{{ route('appointments.index') }}">{{ auth()->user()->isStaff() ? 'Darba panelis' : 'Mans panelis' }}</a>
                @unless (auth()->user()->isStaff())
                    <a href="{{ route('vehicles.index') }}">Mani auto</a>
                @endunless
                <span class="muted">{{ auth()->user()->name }} / {{ auth()->user()->role->value }}</span>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="secondary">Iziet</button>
                </form>
            @endauth
        </nav>
    </div>

    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @yield('content')
</main>
</body>
</html>
