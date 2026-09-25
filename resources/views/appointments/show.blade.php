@extends('layouts.app', ['title' => 'Vizite'])

@section('content')
    <section class="grid">
        <div class="panel">
            <h1>{{ $appointment->vehicle->title() }}</h1>
            <p><strong>Laiks:</strong> {{ $appointment->scheduled_at->format('d.m.Y H:i') }}</p>
            <p><strong>Pakalpojums:</strong> {{ $appointment->service->name }}</p>
            <p><strong>Statuss:</strong> <span class="badge">{{ $appointment->status->label() }}</span></p>
            <p><strong>Klients:</strong> {{ $appointment->user->name }} / {{ $appointment->user->email }}</p>
            @if ($appointment->customer_notes)
                <p><strong>Klienta piezimes:</strong><br>{{ $appointment->customer_notes }}</p>
            @endif
            @if ($appointment->admin_notes)
                <p><strong>Servisa piezimes:</strong><br>{{ $appointment->admin_notes }}</p>
            @endif
            @if ($appointment->invoice)
                <p><strong>Rekins:</strong> {{ $appointment->invoice->formattedTotal() }} ({{ $appointment->invoice->payment_status }})</p>
            @endif
        </div>

        @if (auth()->user()->isStaff())
            <form class="panel stack" method="post" action="{{ route('appointments.status', $appointment) }}">
                @csrf
                @method('PATCH')
                <h2>Admin darbiba</h2>
                <label>
                    Statuss
                    <select name="status">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected($appointment->status === $status)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    @error('status') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>
                    Servisa piezimes
                    <textarea name="admin_notes">{{ old('admin_notes', $appointment->admin_notes) }}</textarea>
                    @error('admin_notes') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>
                    Darbs EUR
                    <input name="labor_eur" type="number" step="0.01" min="0" value="{{ old('labor_eur', $appointment->invoice ? $appointment->invoice->labor_cents / 100 : 0) }}">
                    @error('labor_eur') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>
                    Detalas EUR
                    <input name="parts_eur" type="number" step="0.01" min="0" value="{{ old('parts_eur', $appointment->invoice ? $appointment->invoice->parts_cents / 100 : 0) }}">
                    @error('parts_eur') <span class="error">{{ $message }}</span> @enderror
                </label>
                <button>Atjaunot statusu</button>
            </form>
        @endif
    </section>
@endsection
