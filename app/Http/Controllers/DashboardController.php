<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityMatch;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private WeatherService $weatherService
    ) {}

    /**
     * Show the dashboard.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Haal activiteiten op
        $activities = $user->activities()
            ->where('is_active', true)
            ->withCount('suitableMatches')
            ->latest()
            ->take(3)
            ->get();

        // Haal de beste matches op (gesorteerd op score en datum)
        $upcomingMatches = ActivityMatch::whereHas('activity', function ($query) use ($user) {
                $query->where('user_id', $user->id)->where('is_active', true);
            })
            ->where('is_suitable', true)
            ->where('match_date', '>=', now()->toDateString())
            ->with(['activity', 'weatherForecast'])
            ->orderByDesc('match_score')
            ->orderBy('match_date')
            ->take(10)
            ->get();

        // Statistieken
        $stats = [
            'total_activities' => $user->activities()->count(),
            'active_activities' => $user->activities()->where('is_active', true)->count(),
            'suitable_matches' => ActivityMatch::whereHas('activity', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('is_suitable', true)->count(),
        ];

        return view('dashboard', compact('activities', 'upcomingMatches', 'stats'));
    }

    /**
     * Update weather data from OpenWeatherMap API.
     */
    public function updateWeather(Request $request)
    {
        try {
            $location = $request->input('location');
            
            // Fetch fresh weather data
            $forecasts = $this->weatherService->fetchForecast($location);
            
            if (empty($forecasts)) {
                return redirect()->back()->with('error', 'Kon weergegevens niet ophalen. Controleer je API-sleutel.');
            }
            
            // Find activity matches
            $matchCount = $this->weatherService->findActivityMatches();
            
            return redirect()->back()->with('success', "Weer bijgewerkt! {$matchCount} geschikte matches gevonden.");
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Er ging iets mis: ' . $e->getMessage());
        }
    }
}
