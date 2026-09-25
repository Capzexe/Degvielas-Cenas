@extends('layouts.app', ['title' => 'Pievienot auto'])

@section('content')
    <header>
        <h1>Pievienot auto</h1>
        <p class="muted">Pietiek ar marku un modeli. Parejo vari aizpildit velak.</p>
    </header>

    <form class="panel stack" method="post" action="{{ route('vehicles.store') }}">
        @csrf
        <label>
            Marka
            <input name="make" value="{{ old('make') }}">
            @error('make') <span class="error">{{ $message }}</span> @enderror
        </label>
        <label>
            Modelis
            <input name="model" value="{{ old('model') }}">
            @error('model') <span class="error">{{ $message }}</span> @enderror
        </label>
        <label>
            Gads
            <input name="year" type="number" value="{{ old('year') }}">
            @error('year') <span class="error">{{ $message }}</span> @enderror
        </label>
        <label>
            Valsts numurs
            <input name="registration_number" value="{{ old('registration_number') }}">
            @error('registration_number') <span class="error">{{ $message }}</span> @enderror
        </label>
        <label>
            VIN
            <input name="vin" value="{{ old('vin') }}">
            @error('vin') <span class="error">{{ $message }}</span> @enderror
        </label>
        <button>Saglabat</button>
    </form>
@endsection
