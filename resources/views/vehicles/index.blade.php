@extends('layouts.app', ['title' => 'Mani auto'])

@section('content')
    <header class="topbar">
        <div>
            <h1>Mani auto</h1>
            <p class="muted">Auto saraksts, ko izmantot vizitem.</p>
        </div>
        <a class="button" href="{{ route('vehicles.create') }}">Pievienot auto</a>
    </header>

    <section class="panel">
        @forelse ($vehicles as $vehicle)
            <div class="row">
                <div>
                    <strong>{{ $vehicle->title() }}</strong>
                    <div class="muted">{{ $vehicle->year ?: 'Gads nav noradits' }} {{ $vehicle->vin ? '/ VIN '.$vehicle->vin : '' }}</div>
                </div>
                <form method="post" action="{{ route('vehicles.destroy', $vehicle) }}">
                    @csrf
                    @method('DELETE')
                    <button class="danger">Dzest</button>
                </form>
            </div>
        @empty
            <p class="muted">Vel nav pievienots neviens auto.</p>
        @endforelse
    </section>
@endsection
