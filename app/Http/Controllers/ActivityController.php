<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ActivityController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the user's activities.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $activities = $user->activities()
            ->withCount(['matches', 'suitableMatches'])
            ->latest()
            ->get();

        return view('activities.index', compact('activities'));
    }

    /**
     * Show the form for creating a new activity.
     */
    public function create()
    {
        return view('activities.create');
    }

    /**
     * Store a newly created activity in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'min_temperature' => 'nullable|numeric|min:-50|max:50',
            'max_temperature' => 'nullable|numeric|min:-50|max:50',
            'max_wind_speed' => 'nullable|numeric|min:0|max:200',
            'max_precipitation' => 'nullable|numeric|min:0|max:100',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'preferred_times' => 'nullable|array',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $activity = $user->activities()->create($validated);

        // Check weer matches
        $weatherService = new WeatherService();
        $weatherService->findActivityMatches();

        return redirect()->route('dashboard')
            ->with('success', 'Activiteit succesvol aangemaakt!');
    }

    /**
     * Show the form for editing the specified activity.
     */
    public function edit(Activity $activity)
    {
        $this->authorize('update', $activity);

        return view('activities.edit', compact('activity'));
    }

    /**
     * Update the specified activity in storage.
     */
    public function update(Request $request, Activity $activity)
    {
        $this->authorize('update', $activity);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'min_temperature' => 'nullable|numeric|min:-50|max:50',
            'max_temperature' => 'nullable|numeric|min:-50|max:50',
            'max_wind_speed' => 'nullable|numeric|min:0|max:200',
            'max_precipitation' => 'nullable|numeric|min:0|max:100',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'preferred_times' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $activity->update($validated);

        // Re-check weer matches
        $weatherService = new WeatherService();
        $weatherService->findActivityMatches();

        return redirect()->route('dashboard')
            ->with('success', 'Activiteit bijgewerkt!');
    }

    /**
     * Remove the specified activity from storage.
     */
    public function destroy(Activity $activity)
    {
        $this->authorize('delete', $activity);

        $activity->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Activiteit verwijderd!');
    }
}
