<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentStatusRequest;
use App\Models\Appointment;
use App\Models\Service;
use App\Services\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Appointment::with(['user', 'vehicle', 'service', 'invoice'])->latest('scheduled_at');

        if (! $request->user()->isStaff()) {
            $query->where('user_id', $request->user()->id);
        }

        $appointments = $query->get();

        return view('appointments.index', [
            'appointments' => $appointments,
            'stats' => $this->dashboardStats($request, $appointments),
            'statuses' => AppointmentStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('appointments.create', [
            'vehicles' => $request->user()->vehicles()->latest()->get(),
            'services' => Service::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreAppointmentRequest $request, AppointmentService $service): RedirectResponse
    {
        $appointment = $service->book($request->user(), $request->validated());

        return redirect()->route('appointments.show', $appointment)->with('status', 'Vizite pieteikta.');
    }

    public function show(Request $request, Appointment $appointment): View
    {
        abort_if(! $request->user()->isStaff() && $appointment->user_id !== $request->user()->id, 403);

        return view('appointments.show', [
            'appointment' => $appointment->load(['user', 'vehicle', 'service', 'invoice']),
            'statuses' => AppointmentStatus::cases(),
        ]);
    }

    public function updateStatus(UpdateAppointmentStatusRequest $request, Appointment $appointment, AppointmentService $service): RedirectResponse
    {
        $service->updateStatus(
            $appointment,
            AppointmentStatus::from((string) $request->input('status')),
            $request->input('admin_notes'),
            (int) round(((float) $request->input('labor_eur', 0)) * 100),
            (int) round(((float) $request->input('parts_eur', 0)) * 100),
        );

        return back()->with('status', 'Statuss atjaunots.');
    }

    /**
     * @param \Illuminate\Support\Collection<int, Appointment> $appointments
     * @return array<string, int>
     */
    private function dashboardStats(Request $request, $appointments): array
    {
        if ($request->user()->isStaff()) {
            return [
                'today' => $appointments->filter(fn (Appointment $appointment): bool => $appointment->scheduled_at->isToday())->count(),
                'diagnostics' => $appointments->where('status', AppointmentStatus::Diagnostics)->count(),
                'ready' => $appointments->where('status', AppointmentStatus::Ready)->count(),
                'unpaid' => $appointments->filter(fn (Appointment $appointment): bool => $appointment->invoice?->payment_status === 'unpaid')->count(),
            ];
        }

        return [
            'vehicles' => $request->user()->vehicles()->count(),
            'upcoming' => $appointments->filter(fn (Appointment $appointment): bool => $appointment->scheduled_at->isFuture())->count(),
            'ready' => $appointments->where('status', AppointmentStatus::Ready)->count(),
            'completed' => $appointments->where('status', AppointmentStatus::Completed)->count(),
        ];
    }
}
