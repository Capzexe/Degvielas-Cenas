@extends('layouts.app', ['title' => 'Jauna vizite'])

@section('content')
    <header>
        <h1>Pieteikt viziti</h1>
        <p class="muted">Izvelies auto, servisu un brivu laiku.</p>
    </header>

    @if ($vehicles->isEmpty())
        <div class="panel">
            <p>Vispirms pievieno savu auto.</p>
            <a class="button" href="{{ route('vehicles.create') }}">Pievienot auto</a>
        </div>
    @else
        <form class="panel stack" method="post" action="{{ route('appointments.store') }}">
            @csrf
            <label>
                Auto
                <select name="vehicle_id">
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected(old('vehicle_id') == $vehicle->id)>{{ $vehicle->title() }}</option>
                    @endforeach
                </select>
                @error('vehicle_id') <span class="error">{{ $message }}</span> @enderror
            </label>
            <label>
                Pakalpojums
                <select name="service_id">
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>{{ $service->name }} - {{ $service->formattedPrice() }}</option>
                    @endforeach
                </select>
                @error('service_id') <span class="error">{{ $message }}</span> @enderror
            </label>
            <label>
                Laiks
                <input name="scheduled_at" type="datetime-local" value="{{ old('scheduled_at') }}">
                @error('scheduled_at') <span class="error">{{ $message }}</span> @enderror
            </label>
            <label>
                Piezimes
                <textarea name="customer_notes">{{ old('customer_notes') }}</textarea>
                @error('customer_notes') <span class="error">{{ $message }}</span> @enderror
            </label>
            <button>Saglabat</button>
        </form>
    @endif
@endsection
