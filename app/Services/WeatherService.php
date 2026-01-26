<?php

namespace App\Services;

use App\Models\WeatherForecast;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class WeatherService
{
    private string $apiKey;
    private string $baseUrl;
    private string $defaultLocation;
    private string $defaultCountry;

    public function __construct()
    {
        $this->apiKey = config('weather.api_key');
        $this->baseUrl = config('weather.api_url');
        $this->defaultLocation = config('weather.default_location');
        $this->defaultCountry = config('weather.default_country');
    }

    /**
     * Haal weersvoorspelling op voor de komende dagen.
     */
    public function fetchForecast(?string $location = null, ?int $days = null): array
    {
        // Use defaults if not provided
        $location = $location ?? $this->defaultLocation;
        $days = $days ?? config('weather.forecast_days', 5);
        
        // Check if API key is configured
        if (empty($this->apiKey)) {
            Log::warning('Weather API key not configured, using dummy data');
            return $this->generateDummyForecast($days);
        }

        // Try to get from cache first
        $cacheKey = "weather_forecast_{$location}_{$days}";
        
        if (config('weather.cache_enabled') && Cache::has($cacheKey)) {
            Log::info('Using cached weather data', ['location' => $location]);
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout(10)
                ->retry(3, 1000) // Retry 3x met 1 seconde wachttijd
                ->get("{$this->baseUrl}/forecast", [
                    'q' => $location . ',' . $this->defaultCountry,
                    'appid' => $this->apiKey,
                    'units' => config('weather.units', 'metric'),
                    'lang' => config('weather.language', 'nl'),
                    'cnt' => $days * 8, // 8 metingen per dag (elke 3 uur)
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Valideer API response structuur
                if (!isset($data['list']) || !is_array($data['list'])) {
                    Log::error('Invalid API response structure', [
                        'location' => $location,
                        'response' => $data
                    ]);
                    return $this->generateDummyForecast($days);
                }
                
                if (empty($data['list'])) {
                    Log::warning('Empty forecast data received', ['location' => $location]);
                    return $this->generateDummyForecast($days);
                }
                
                $forecasts = $this->parseForecastData($data);
                
                if (empty($forecasts)) {
                    Log::warning('No forecasts parsed from API response', ['location' => $location]);
                    return $this->generateDummyForecast($days);
                }
                
                // Cache the results
                if (config('weather.cache_enabled')) {
                    Cache::put($cacheKey, $forecasts, config('weather.cache_duration', 1800));
                }
                
                Log::info('Weather data fetched successfully', [
                    'location' => $location,
                    'count' => count($forecasts),
                    'date_range' => [
                        'from' => $forecasts[0]->forecast_date ?? 'unknown',
                        'to' => end($forecasts)->forecast_date ?? 'unknown'
                    ]
                ]);
                
                return $forecasts;
            }

            // Specifieke error handling op basis van status code
            $statusCode = $response->status();
            $errorMessage = $this->getApiErrorMessage($statusCode, $response->json());
            
            Log::error('Weather API error', [
                'status' => $statusCode,
                'message' => $errorMessage,
                'location' => $location,
                'response' => $response->body()
            ]);
            
            // Fallback to dummy data on error
            return $this->generateDummyForecast($days);

        } catch (\Exception $e) {
            Log::error('Weather fetch failed', [
                'error' => $e->getMessage(),
                'location' => $location
            ]);
            
            // Fallback to dummy data on exception
            return $this->generateDummyForecast($days);
        }
    }

    /**
     * Get API error message based on status code.
     */
    private function getApiErrorMessage(int $statusCode, ?array $data = null): string
    {
        $message = $data['message'] ?? '';
        
        return match ($statusCode) {
            401 => 'Ongeldige API key. Controleer je WEATHER_API_KEY in .env',
            404 => "Locatie niet gevonden: {$message}",
            429 => 'API limiet bereikt. Probeer het later opnieuw.',
            500, 502, 503 => 'Weather API server probleem. Probeer het later.',
            default => "API fout ({$statusCode}): {$message}"
        };
    }

    /**
     * Parse en sla weersgegevens op in database.
     */
    private function parseForecastData(array $data): array
    {
        $forecasts = [];

        if (!isset($data['list']) || !is_array($data['list'])) {
            Log::warning('Invalid forecast data structure: missing list');
            return $forecasts;
        }

        $location = $data['city']['name'] ?? $this->defaultLocation;

        foreach ($data['list'] as $index => $item) {
            try {
                // Valideer verplichte velden
                if (!isset($item['dt']) || !isset($item['main']['temp'])) {
                    Log::warning('Skipping forecast item: missing required fields', [
                        'index' => $index,
                        'item' => $item
                    ]);
                    continue;
                }
                
                $datetime = Carbon::createFromTimestamp($item['dt']);
                
                // Extract precipitation data (rain or snow)
                $precipitation = 0;
                if (isset($item['rain']['3h'])) {
                    $precipitation = $item['rain']['3h'];
                } elseif (isset($item['snow']['3h'])) {
                    $precipitation = $item['snow']['3h'];
                }
                
                // Valideer data ranges
                $temperature = $item['main']['temp'];
                $windSpeed = ($item['wind']['speed'] ?? 0) * 3.6; // m/s naar km/h
                
                if ($temperature < -50 || $temperature > 60) {
                    Log::warning('Unrealistic temperature value', [
                        'temperature' => $temperature,
                        'datetime' => $datetime
                    ]);
                }
                
                $forecast = WeatherForecast::updateOrCreate(
                    [
                        'forecast_date' => $datetime->toDateString(),
                        'forecast_time' => $datetime->format('H:i:s'),
                        'location' => $location,
                    ],
                    [
                        'temperature' => round($temperature, 2),
                        'wind_speed' => round($windSpeed, 2),
                        'precipitation' => round($precipitation, 2),
                        'humidity' => isset($item['main']['humidity']) ? (int)$item['main']['humidity'] : null,
                        'condition' => $item['weather'][0]['main'] ?? null,
                        'description' => $item['weather'][0]['description'] ?? null,
                    ]
                );

                $forecasts[] = $forecast;
                
            } catch (\Exception $e) {
                Log::error('Failed to parse forecast item', [
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'item' => $item
                ]);
                continue;
            }
        }

        return $forecasts;
    }

    /**
     * Genereer dummy data voor testing (als je geen API key hebt).
     */
    public function generateDummyForecast(int $days = 7): array
    {
        $forecasts = [];
        $conditions = ['Clear', 'Clouds', 'Rain', 'Drizzle'];
        $descriptions = ['helder', 'bewolkt', 'regen', 'motregen'];

        for ($day = 0; $day < $days; $day++) {
            for ($hour = 6; $hour <= 21; $hour += 3) {
                $date = Carbon::now()->addDays($day);
                
                $forecast = WeatherForecast::updateOrCreate(
                    [
                        'forecast_date' => $date->toDateString(),
                        'forecast_time' => sprintf('%02d:00:00', $hour),
                        'location' => 'Nederland',
                    ],
                    [
                        'temperature' => rand(5, 25),
                        'wind_speed' => rand(5, 40),
                        'precipitation' => rand(0, 100) > 70 ? rand(0, 10) : 0,
                        'humidity' => rand(40, 90),
                        'condition' => $conditions[array_rand($conditions)],
                        'description' => $descriptions[array_rand($descriptions)],
                    ]
                );

                $forecasts[] = $forecast;
            }
        }

        return $forecasts;
    }

    /**
     * Vind matches tussen activiteiten en weer.
     * 
     * @param int|null $specificActivityId Optioneel: zoek alleen matches voor deze specifieke activiteit
     * @return array ['count' => int, 'firstMatch' => ActivityMatch|null]
     */
    public function findActivityMatches(?int $specificActivityId = null): array
    {
        $query = \App\Models\Activity::where('is_active', true);
        
        if ($specificActivityId) {
            $query->where('id', $specificActivityId);
        }
        
        $activities = $query->get();
        $matchCount = 0;
        $firstMatchFound = null;

        Log::info('Starting activity matching', [
            'total_activities' => $activities->count()
        ]);

        foreach ($activities as $activity) {
            // Check of deze activiteit al een notificatie heeft ontvangen
            $hasNotification = \App\Models\ActivityMatch::where('activity_id', $activity->id)
                ->where('user_notified', true)
                ->exists();

            // Skip als er al een notificatie is verstuurd voor deze activiteit
            if ($hasNotification) {
                Log::debug('Skipping activity - already notified', [
                    'activity_id' => $activity->id,
                    'activity_name' => $activity->name
                ]);
                continue;
            }

            // Haal weersvoorspellingen op voor de locatie, gesorteerd op datum
            $forecasts = WeatherForecast::upcoming()
                ->where('location', $activity->location)
                ->orderBy('forecast_date')
                ->orderBy('forecast_time')
                ->get();

            if ($forecasts->isEmpty()) {
                Log::warning('No forecasts found for location', [
                    'activity_id' => $activity->id,
                    'location' => $activity->location
                ]);
                continue;
            }

            Log::debug('Checking forecasts for activity', [
                'activity_id' => $activity->id,
                'activity_name' => $activity->name,
                'location' => $activity->location,
                'forecast_count' => $forecasts->count(),
                'duration_hours' => $activity->duration_hours,
                'criteria' => [
                    'temp_range' => $activity->min_temperature . '-' . $activity->max_temperature . '°C',
                    'max_wind' => $activity->max_wind_speed . ' km/h',
                    'max_precip' => ($activity->max_precipitation ?? 0) . ' mm'
                ]
            ]);

            // Converteer forecasts naar array voor duration checks
            $forecastsArray = $forecasts->all();

            // Zoek de EERSTE geschikte dag (rekening houdend met duur)
            $firstSuitableMatch = null;
            $suitableCount = 0;
            
            foreach ($forecasts as $forecast) {
                // Check of het weer geschikt is voor de hele duur van de activiteit
                $isSuitable = $activity->matchesWeatherDuration($forecast, $forecastsArray);
                $score = $activity->calculateMatchScore($forecast, $forecastsArray);

                if ($isSuitable) {
                    $suitableCount++;
                }

                // Sla match op in database
                \App\Models\ActivityMatch::updateOrCreate(
                    [
                        'activity_id' => $activity->id,
                        'weather_forecast_id' => $forecast->id,
                    ],
                    [
                        'match_date' => $forecast->forecast_date,
                        'match_time' => $forecast->forecast_time,
                        'match_score' => $score,
                        'is_suitable' => $isSuitable,
                    ]
                );

                // Als dit de eerste geschikte match is, sla hem op
                if ($isSuitable && !$firstSuitableMatch) {
                    $firstSuitableMatch = \App\Models\ActivityMatch::where([
                        'activity_id' => $activity->id,
                        'weather_forecast_id' => $forecast->id,
                    ])->first();
                    
                    $startDateTime = \Carbon\Carbon::parse($forecast->forecast_date->format('Y-m-d') . ' ' . $forecast->forecast_time);
                    $endTime = $startDateTime->copy()
                        ->addHours($activity->duration_hours)
                        ->format('H:i');
                    
                    Log::info('First suitable match found', [
                        'activity_name' => $activity->name,
                        'match_date' => $forecast->forecast_date,
                        'match_time' => $forecast->forecast_time,
                        'duration_hours' => $activity->duration_hours,
                        'end_time' => $endTime,
                        'temperature' => $forecast->temperature,
                        'wind_speed' => $forecast->wind_speed,
                        'precipitation' => $forecast->precipitation,
                        'score' => $score
                    ]);
                }
            }

            Log::info('Activity matching complete', [
                'activity_id' => $activity->id,
                'activity_name' => $activity->name,
                'suitable_matches' => $suitableCount,
                'will_notify' => $firstSuitableMatch ? 'yes' : 'no'
            ]);

            // Verstuur 1 notificatie voor de EERSTE geschikte dag
            if ($firstSuitableMatch) {
                try {
                    $activity->user->notify(new \App\Notifications\ActivityMatchFound($firstSuitableMatch));
                    $firstSuitableMatch->update(['user_notified' => true]);
                    $matchCount++;
                    
                    // Sla de allereerste match op voor directe feedback
                    if (!$firstMatchFound) {
                        $firstMatchFound = $firstSuitableMatch;
                    }
                    
                    Log::info('Notification sent', [
                        'activity_id' => $activity->id,
                        'user_email' => $activity->user->email
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send notification', [
                        'activity_id' => $activity->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        Log::info('Activity matching finished', [
            'notifications_sent' => $matchCount
        ]);

        return [
            'count' => $matchCount,
            'firstMatch' => $firstMatchFound
        ];
    }

    /**
     * Test API connectivity and configuration.
     */
    public function testApiConnection(): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'details' => []
        ];

        // Check API key
        if (empty($this->apiKey)) {
            $result['message'] = 'API key niet geconfigureerd';
            $result['details']['api_key'] = 'Missing';
            return $result;
        }

        $result['details']['api_key'] = 'Configured (length: ' . strlen($this->apiKey) . ')';
        $result['details']['api_url'] = $this->baseUrl;
        $result['details']['default_location'] = $this->defaultLocation;

        try {
            // Test API call met timeout
            $response = Http::timeout(5)->get("{$this->baseUrl}/weather", [
                'q' => $this->defaultLocation,
                'appid' => $this->apiKey,
                'units' => 'metric'
            ]);

            $result['details']['http_status'] = $response->status();

            if ($response->successful()) {
                $data = $response->json();
                $result['success'] = true;
                $result['message'] = 'API verbinding succesvol!';
                $result['details']['location'] = $data['name'] ?? 'Unknown';
                $result['details']['temp'] = isset($data['main']['temp']) ? round($data['main']['temp'], 1) . '°C' : 'N/A';
                $result['details']['response_time'] = 'OK';
            } else {
                $result['message'] = $this->getApiErrorMessage($response->status(), $response->json());
                $result['details']['response_body'] = $response->body();
            }

        } catch (\Exception $e) {
            $result['message'] = 'Verbindingsfout: ' . $e->getMessage();
            $result['details']['error'] = $e->getMessage();
        }

        return $result;
    }
}
