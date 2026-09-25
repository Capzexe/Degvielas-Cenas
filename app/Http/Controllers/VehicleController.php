<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        return view('vehicles.index', [
            'vehicles' => $request->user()->vehicles()->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('vehicles.create');
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $request->user()->vehicles()->create($request->validated());

        return redirect()->route('vehicles.index')->with('status', 'Auto pievienots.');
    }

    public function destroy(Request $request, Vehicle $vehicle): RedirectResponse
    {
        abort_if($vehicle->user_id !== $request->user()->id, 403);

        $vehicle->delete();

        return back()->with('status', 'Auto izdzests.');
    }
}
