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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Check of gebruiker de limiet heeft bereikt
        $activityCount = $user->activities()->count();
        if ($activityCount >= $user->max_activities) {
            return redirect()->route('activities.index')
                ->with('error', 'Je hebt het maximale aantal activiteiten bereikt. Upgrade naar Premium voor onbeperkt activiteiten!');
        }
        
        return view('activities.create');
    }

    /**
     * Store a newly created activity in storage.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Check of gebruiker de limiet heeft bereikt
        $activityCount = $user->activities()->count();
        if ($activityCount >= $user->max_activities) {
            return redirect()->route('activities.index')
                ->with('error', 'Je hebt het maximale aantal activiteiten bereikt. Upgrade naar Premium voor onbeperkt activiteiten!');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'min_temperature' => 'nullable|numeric|min:-50|max:50',
            'max_temperature' => 'nullable|numeric|min:-50|max:50',
            'max_wind_speed' => 'nullable|numeric|min:0|max:200',
            'max_precipitation' => 'nullable|numeric|min:0|max:100',
            'duration_hours' => 'required|integer|min:1|max:24',
            'preferred_times' => 'nullable|array',
        ]);

        $activity = $user->activities()->create($validated);

        // Haal weergegevens op voor de locatie van deze activiteit
        $weatherService = new WeatherService();
        $weatherService->fetchForecast($activity->location);
        
        // Check weer matches voor deze specifieke activiteit
        $matchResult = $weatherService->findActivityMatches($activity->id);
        
        // Als er meteen een match gevonden is, toon popup
        if ($matchResult['firstMatch']) {
            $match = $matchResult['firstMatch'];
            $matchData = $this->formatMatchData($match);
            
            return redirect()->route('dashboard')
                ->with('success', 'Activiteit succesvol aangemaakt!')
                ->with('immediate_match', $matchData);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Activiteit succesvol aangemaakt!')
            ->with('info', 'We zijn nu op zoek naar geschikte dagen voor je activiteit. Je ontvangt een melding zodra we een match vinden!');
    }
    
    /**
     * Format match data for frontend popup.
     */
    private function formatMatchData(\App\Models\ActivityMatch $match): array
    {
        $activity = $match->activity;
        $weather = $match->weatherForecast;
        $matchDate = \Carbon\Carbon::parse($match->match_date);
        $matchTime = \Carbon\Carbon::parse($match->match_time);
        
        return [
            'activityName' => $activity->name,
            'date' => $matchDate->isoFormat('dddd D MMMM YYYY'),
            'time' => $matchTime->format('H:i') . ' - ' . $matchTime->copy()->addHours($activity->duration_hours)->format('H:i') . ' uur',
            'temperature' => $weather->temperature . '°C',
            'windSpeed' => $weather->wind_speed . ' km/h',
            'precipitation' => $weather->precipitation . ' mm',
        ];
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
            'location' => 'required|string|max:255',
            'min_temperature' => 'nullable|numeric|min:-50|max:50',
            'max_temperature' => 'nullable|numeric|min:-50|max:50',
            'max_wind_speed' => 'nullable|numeric|min:0|max:200',
            'max_precipitation' => 'nullable|numeric|min:0|max:100',
            'duration_hours' => 'required|integer|min:1|max:24',
            'preferred_times' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $activity->update($validated);

        // Haal weergegevens op voor de (mogelijk gewijzigde) locatie
        $weatherService = new WeatherService();
        $weatherService->fetchForecast($activity->location);
        
        // Re-check weer matches voor deze specifieke activiteit
        $matchResult = $weatherService->findActivityMatches($activity->id);
        
        // Als er meteen een match gevonden is, toon popup
        if ($matchResult['firstMatch']) {
            $match = $matchResult['firstMatch'];
            $matchData = $this->formatMatchData($match);
            
            return redirect()->route('dashboard')
                ->with('success', 'Activiteit bijgewerkt!')
                ->with('immediate_match', $matchData);
        }

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
