@extends('layouts.app', ['title' => auth()->user()->isStaff() ? 'Admina panelis' : 'Klienta panelis'])

@section('content')
    @if (auth()->user()->isStaff())
        <header class="dashboard-head">
            <div>
                <h1>Servisa darba panelis</h1>
                <p class="muted">Seko rindai, maini remonta statusus un pieraksti izmaksas, kad darbs kustas uz prieksu.</p>
            </div>
            <a class="button secondary" href="{{ route('appointments.index') }}">Atsvaidzinat rindu</a>
        </header>

        <section class="metric-grid">
            <div class="metric"><strong>{{ $stats['today'] }}</strong><span>Sodien servisa grafika</span></div>
            <div class="metric"><strong>{{ $stats['diagnostics'] }}</strong><span>Diagnostika</span></div>
            <div class="metric"><strong>{{ $stats['ready'] }}</strong><span>Gatavi sanemsanai</span></div>
            <div class="metric"><strong>{{ $stats['unpaid'] }}</strong><span>Neapmaksati rekini</span></div>
        </section>

        <section class="panel">
            <div class="section-title">
                <h2>Darba rinda</h2>
                <p class="muted">Atver viziti, lai nomainitu statusu vai pievienotu darba un detalu izmaksas.</p>
            </div>
            @forelse ($appointments as $appointment)
                <div class="row">
                    <div>
                        <strong>{{ $appointment->scheduled_at->format('d.m.Y H:i') }}</strong>
                        <div>{{ $appointment->vehicle->title() }} - {{ $appointment->service->name }}</div>
                        <div class="muted">{{ $appointment->user->name }} / {{ $appointment->user->email }}</div>
                    </div>
                    <div class="actions">
                        <span class="badge">{{ $appointment->status->label() }}</span>
                        @if ($appointment->invoice)
                            <span class="badge">{{ $appointment->invoice->formattedTotal() }}</span>
                        @endif
                        <a class="button secondary" href="{{ route('appointments.show', $appointment) }}">Apstradat</a>
                    </div>
                </div>
            @empty
                <p class="muted">Servisa rinda ir tuksa.</p>
            @endforelse
        </section>
    @else
        <header class="dashboard-head">
            <div>
                <h1>Mans servisa panelis</h1>
                <p class="muted">Te redzi savus auto, pieteiktas vizites un servisa statusu bez zvanisanas uz darbnicu.</p>
            </div>
            <div class="actions">
                <a class="button secondary" href="{{ route('vehicles.create') }}">Pievienot auto</a>
                <a class="button" href="{{ route('appointments.create') }}">Pieteikt viziti</a>
            </div>
        </header>

        <section class="metric-grid">
            <div class="metric"><strong>{{ $stats['vehicles'] }}</strong><span>Mani auto</span></div>
            <div class="metric"><strong>{{ $stats['upcoming'] }}</strong><span>Aktivas vizites</span></div>
            <div class="metric"><strong>{{ $stats['ready'] }}</strong><span>Gatavs sanemsanai</span></div>
            <div class="metric"><strong>{{ $stats['completed'] }}</strong><span>Pabeigti darbi</span></div>
        </section>

        <section class="panel">
            <div class="section-title">
                <h2>Manas vizites</h2>
                <p class="muted">Atver viziti, lai redzetu servisa piezimes, statusu un rekinu.</p>
            </div>
            @forelse ($appointments as $appointment)
                <div class="row">
                    <div>
                        <strong>{{ $appointment->scheduled_at->format('d.m.Y H:i') }}</strong>
                        <div>{{ $appointment->vehicle->title() }} - {{ $appointment->service->name }}</div>
                        <div class="muted">{{ $appointment->customer_notes ?: 'Bez papildus piezimem' }}</div>
                    </div>
                    <div class="actions">
                        <span class="badge">{{ $appointment->status->label() }}</span>
                        @if ($appointment->invoice)
                            <span class="badge">{{ $appointment->invoice->formattedTotal() }}</span>
                        @endif
                        <a class="button secondary" href="{{ route('appointments.show', $appointment) }}">Skatit</a>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <h2>Vel nav nevienas vizites</h2>
                    <p class="muted">Sakuma pievieno auto, pec tam piesaki pirmo servisa laiku.</p>
                    <div class="actions">
                        <a class="button secondary" href="{{ route('vehicles.create') }}">Pievienot auto</a>
                        <a class="button" href="{{ route('appointments.create') }}">Pieteikt viziti</a>
                    </div>
                </div>
            @endforelse
        </section>
    @endif
@endsection
