@extends('layouts.app', ['title' => 'Ielogoties'])

@section('content')
    <section class="grid">
        <div class="panel">
            <h1>Autoservisa rezervacijas</h1>
            <p class="muted">Mazs portfolio projekts ar lomam, auto, vizitem, rekiniem, rindas job un WebSocket event paraugu.</p>
            <div class="actions">
                <form method="post" action="{{ route('demo-login', 'customer') }}">
                    @csrf
                    <button>Demo klients</button>
                </form>
                <form method="post" action="{{ route('demo-login', 'admin') }}">
                    @csrf
                    <button class="secondary">Demo admins</button>
                </form>
            </div>
        </div>

        <form class="panel stack" method="post" action="{{ route('login.store') }}">
            @csrf
            <h2>Ielogoties manuali</h2>
            <label>
                E-pasts
                <input name="email" type="email" value="{{ old('email', 'client@example.com') }}">
                @error('email') <span class="error">{{ $message }}</span> @enderror
            </label>
            <label>
                Parole
                <input name="password" type="password" value="password">
                @error('password') <span class="error">{{ $message }}</span> @enderror
            </label>
            <button>Ielogoties</button>
        </form>
    </section>
@endsection
