<?php

namespace App\Services;

use App\Models\WeatherForecast;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WeatherService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        // Voor nu gebruiken we OpenWeatherMap API
        // Je kunt ook Buienradar API gebruiken
        $this->apiKey = env('WEATHER_API_KEY', '');
        $this->baseUrl = 'https://api.openweathermap.org/data/2.5';
    }

    /**
     * Haal weersvoorspelling op voor de komende dagen.
     */
    public function fetchForecast(string $location = 'Amsterdam', int $days = 5): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/forecast", [
                'q' => $location . ',NL',
                'appid' => $this->apiKey,
                'units' => 'metric', // Voor Celsius
                'lang' => 'nl',
                'cnt' => $days * 8, // 8 metingen per dag (elke 3 uur)
            ]);

            if ($response->successful()) {
                return $this->parseForecastData($response->json());
            }

            Log::error('Weather API error', ['response' => $response->body()]);
            return [];

        } catch (\Exception $e) {
            Log::error('Weather fetch failed', ['error' => $e->getMessage()]);
            return [];
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

        foreach ($data['list'] as $item) {
            $datetime = Carbon::createFromTimestamp($item['dt']);
            
            $forecast = WeatherForecast::updateOrCreate(
                [
                    'forecast_date' => $datetime->toDateString(),
                    'forecast_time' => $datetime->format('H:i:s'),
                    'location' => $data['city']['name'] ?? 'Nederland',
                ],
                [
                    'temperature' => $item['main']['temp'],
                    'wind_speed' => ($item['wind']['speed'] ?? 0) * 3.6, // m/s naar km/h
                    'precipitation' => $item['rain']['3h'] ?? 0,
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
