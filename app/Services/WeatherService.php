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
    public function fetchForecast(string $location = null, int $days = null): array
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
            $response = Http::timeout(10)->get("{$this->baseUrl}/forecast", [
                'q' => $location . ',' . $this->defaultCountry,
                'appid' => $this->apiKey,
                'units' => config('weather.units', 'metric'),
                'lang' => config('weather.language', 'nl'),
                'cnt' => $days * 8, // 8 metingen per dag (elke 3 uur)
            ]);

            if ($response->successful()) {
                $forecasts = $this->parseForecastData($response->json());
                
                // Cache the results
                if (config('weather.cache_enabled')) {
                    Cache::put($cacheKey, $forecasts, config('weather.cache_duration', 1800));
                }
                
                Log::info('Weather data fetched successfully', [
                    'location' => $location,
                    'count' => count($forecasts)
                ]);
                
                return $forecasts;
            }

            Log::error('Weather API error', [
                'status' => $response->status(),
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
     * Parse en sla weersgegevens op in database.
     */
    private function parseForecastData(array $data): array
    {
        $forecasts = [];

        if (!isset($data['list'])) {
            return $forecasts;
        }

        $location = $data['city']['name'] ?? $this->defaultLocation;

        foreach ($data['list'] as $item) {
            $datetime = Carbon::createFromTimestamp($item['dt']);
            
            // Extract precipitation data (rain or snow)
            $precipitation = 0;
            if (isset($item['rain']['3h'])) {
                $precipitation = $item['rain']['3h'];
            } elseif (isset($item['snow']['3h'])) {
                $precipitation = $item['snow']['3h'];
            }
            
            $forecast = WeatherForecast::updateOrCreate(
                [
                    'forecast_date' => $datetime->toDateString(),
                    'forecast_time' => $datetime->format('H:i:s'),
                    'location' => $location,
                ],
                [
                    'temperature' => round($item['main']['temp'], 2),
                    'wind_speed' => round(($item['wind']['speed'] ?? 0) * 3.6, 2), // m/s naar km/h
                    'precipitation' => round($precipitation, 2),
                    'humidity' => $item['main']['humidity'] ?? null,
                    'condition' => $item['weather'][0]['main'] ?? null,
                    'description' => $item['weather'][0]['description'] ?? null,
                ]
            );

            $forecasts[] = $forecast;
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
     */
    public function findActivityMatches(): int
    {
        $activities = \App\Models\Activity::where('is_active', true)->get();
        $forecasts = WeatherForecast::upcoming()->get();
        $matchCount = 0;

        foreach ($activities as $activity) {
            foreach ($forecasts as $forecast) {
                $isSuitable = $activity->matchesWeather($forecast);
                $score = $activity->calculateMatchScore($forecast);

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

                if ($isSuitable) {
                    $matchCount++;
                }
            }
        }

        return $matchCount;
    }
}
