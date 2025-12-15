<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Lake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TripController extends Controller
{
    public function index()
    {
        $trips = Trip::with('lake')
            ->where('user_id', Auth::id())
            ->orderBy('trip_date')
            ->get();

        return Inertia::render('Trips/Index', [
            'trips' => $trips,
        ]);
    }

    public function create()
    {
        $favoriteLakes = Auth::user()
            ->favoriteLakes()
            ->select('lakes.id', 'lakes.name', 'lakes.slug', 'lakes.region')
            ->orderBy('lakes.name')
            ->get();

        return Inertia::render('Trips/Create', [
            'favoriteLakes' => $favoriteLakes,
        ]);
    }

    public function store(Request $request)
    {
        $favoriteLakeIds = Auth::user()
            ->favoriteLakes()
            ->pluck('lakes.id')
            ->toArray();

        $data = $request->validate([
            'lake_id' => ['required', 'integer', 'in:' . implode(',', $favoriteLakeIds)],
            'trip_date' => ['required', 'date'],
            'time_of_day' => ['nullable', 'string', 'max:50'],
            'min_thickness_inches' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'avoid_slush' => ['boolean'],
            'avoid_pressure_cracks' => ['boolean'],
            'target_species' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['user_id'] = Auth::id();
        $data['avoid_slush'] = $request->boolean('avoid_slush');
        $data['avoid_pressure_cracks'] = $request->boolean('avoid_pressure_cracks');

        Trip::create($data);

        return redirect()
            ->route('trips.index')
            ->with('success', 'Trip created.');
    }

    public function edit(Trip $trip)
    {
        if ($trip->user_id !== Auth::id()) {
            abort(403);
        }

        $favoriteLakes = Auth::user()
            ->favoriteLakes()
            ->select('lakes.id', 'lakes.name', 'lakes.slug', 'lakes.region')
            ->orderBy('lakes.name')
            ->get();

        return Inertia::render('Trips/Edit', [
            'trip' => $trip->load('lake'),
            'favoriteLakes' => $favoriteLakes,
        ]);
    }

    public function update(Request $request, Trip $trip)
    {
        if ($trip->user_id !== Auth::id()) {
            abort(403);
        }

        $favoriteLakeIds = Auth::user()
            ->favoriteLakes()
            ->pluck('lakes.id')
            ->toArray();

        $data = $request->validate([
            'lake_id' => ['required', 'integer', 'in:' . implode(',', $favoriteLakeIds)],
            'trip_date' => ['required', 'date'],
            'time_of_day' => ['nullable', 'string', 'max:50'],
            'min_thickness_inches' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'avoid_slush' => ['boolean'],
            'avoid_pressure_cracks' => ['boolean'],
            'target_species' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['avoid_slush'] = $request->boolean('avoid_slush');
        $data['avoid_pressure_cracks'] = $request->boolean('avoid_pressure_cracks');

        $trip->update($data);

        return redirect()
            ->route('trips.index')
            ->with('success', 'Trip updated.');
    }
}
