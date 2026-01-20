<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'location',
        'min_temperature',
        'max_temperature',
        'max_wind_speed',
        'max_precipitation',
        'duration_hours',
        'is_active',
        'preferred_times',
    ];

    protected $casts = [
        'min_temperature' => 'decimal:2',
        'max_temperature' => 'decimal:2',
        'max_wind_speed' => 'decimal:2',
        'max_precipitation' => 'decimal:2',
        'is_active' => 'boolean',
        'preferred_times' => 'array',
    ];

    /**
     * Get the user that owns the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the matches for this activity.
     */
    public function matches(): HasMany
    {
        return $this->hasMany(ActivityMatch::class);
    }

    /**
     * Get suitable matches only.
     */
    public function suitableMatches(): HasMany
    {
        return $this->hasMany(ActivityMatch::class)->where('is_suitable', true);
    }

    /**
     * Check if weather conditions match activity requirements.
     * This checks a single forecast point.
     */
    public function matchesWeather(WeatherForecast $forecast): bool
    {
        // Check temperature
        if ($this->min_temperature !== null && $forecast->temperature < $this->min_temperature) {
            return false;
        }

        if ($this->max_temperature !== null && $forecast->temperature > $this->max_temperature) {
            return false;
        }

        // Check wind speed
        if ($this->max_wind_speed !== null && $forecast->wind_speed > $this->max_wind_speed) {
            return false;
        }

        // Check precipitation - gebruik 0 als default als max_precipitation null is
        $maxPrecip = $this->max_precipitation ?? 0;
        if ($forecast->precipitation > $maxPrecip) {
            return false;
        }

        return true;
    }

    /**
     * Check if weather is suitable for the entire duration of the activity.
     * 
     * @param WeatherForecast $startForecast De start forecast
     * @param array $allForecasts Alle beschikbare forecasts voor de locatie
     * @return bool True als het weer gedurende de hele duur geschikt is
     */
    public function matchesWeatherDuration(WeatherForecast $startForecast, array $allForecasts): bool
    {
        // Check de start forecast eerst
        if (!$this->matchesWeather($startForecast)) {
            return false;
        }

        // Als duur <= 3 uur, is één forecast voldoende (forecasts zijn per 3 uur)
        if ($this->duration_hours <= 3) {
            return true;
        }

        // Voor langere activiteiten, check alle forecasts in de duur periode
        $startDateTime = \Carbon\Carbon::parse($startForecast->forecast_date->format('Y-m-d') . ' ' . $startForecast->forecast_time);
        $endDateTime = $startDateTime->copy()->addHours($this->duration_hours);

        // Filter forecasts die binnen de activiteit periode vallen
        $relevantForecasts = array_filter($allForecasts, function($forecast) use ($startDateTime, $endDateTime) {
            $forecastDateTime = \Carbon\Carbon::parse($forecast->forecast_date->format('Y-m-d') . ' ' . $forecast->forecast_time);
            return $forecastDateTime->gte($startDateTime) && $forecastDateTime->lt($endDateTime);
        });

        // Alle relevante forecasts moeten aan de criteria voldoen
        foreach ($relevantForecasts as $forecast) {
            if (!$this->matchesWeather($forecast)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate match score (0-100) based on how well weather fits.
     * For activities longer than 3 hours, this averages scores across all forecast periods.
     * 
     * @param WeatherForecast $startForecast De start forecast
     * @param array $allForecasts Alle beschikbare forecasts (optioneel, voor langere activiteiten)
     * @return int Score tussen 0-100
     */
    public function calculateMatchScore(WeatherForecast $startForecast, array $allForecasts = []): int
    {
        // Voor korte activiteiten (<=3 uur), score alleen de start forecast
        if ($this->duration_hours <= 3 || empty($allForecasts)) {
            return $this->calculateSingleForecastScore($startForecast);
        }

        // Voor langere activiteiten, bereken gemiddelde score over de hele duur
        $startDateTime = \Carbon\Carbon::parse($startForecast->forecast_date->format('Y-m-d') . ' ' . $startForecast->forecast_time);
        $endDateTime = $startDateTime->copy()->addHours($this->duration_hours);

        $relevantForecasts = array_filter($allForecasts, function($forecast) use ($startDateTime, $endDateTime) {
            $forecastDateTime = \Carbon\Carbon::parse($forecast->forecast_date->format('Y-m-d') . ' ' . $forecast->forecast_time);
            return $forecastDateTime->gte($startDateTime) && $forecastDateTime->lt($endDateTime);
        });

        if (empty($relevantForecasts)) {
            return $this->calculateSingleForecastScore($startForecast);
        }

        // Bereken gemiddelde score
        $totalScore = 0;
        $count = 0;
        foreach ($relevantForecasts as $forecast) {
            $totalScore += $this->calculateSingleForecastScore($forecast);
            $count++;
        }

        return $count > 0 ? (int)round($totalScore / $count) : 0;
    }

    /**
     * Calculate score for a single forecast.
     */
    private function calculateSingleForecastScore(WeatherForecast $forecast): int
    {
        $score = 100;

        // Temperature scoring
        if ($this->min_temperature !== null || $this->max_temperature !== null) {
            $idealTemp = ($this->min_temperature + $this->max_temperature) / 2;
            $tempDiff = abs($forecast->temperature - $idealTemp);
            $score -= min($tempDiff * 5, 30); // Max 30 points deduction
        }

        // Wind scoring
        if ($this->max_wind_speed !== null && $this->max_wind_speed > 0) {
            $windRatio = $forecast->wind_speed / $this->max_wind_speed;
            if ($windRatio > 1) {
                $score -= 30;
            } else {
                $score -= ($windRatio * 10);
            }
        }

        // Precipitation scoring
        if ($forecast->precipitation > $this->max_precipitation) {
            $score -= 40;
        } else if ($forecast->precipitation > 0) {
            $score -= 10;
        }

        return max(0, min(100, (int)$score));
    }

    /**
     * Krijg de eerste geschikte dag waarop deze activiteit uitgevoerd kan worden.
     */
    public function getBestMatchDate(): ?array
    {
        $bestMatch = $this->matches()
            ->where('is_suitable', true)
            ->orderBy('match_date', 'asc')
            ->orderBy('match_time', 'asc')
            ->with('weatherForecast')
            ->first();

        if (!$bestMatch) {
            return null;
        }

        return [
            'date' => $bestMatch->match_date,
            'time' => $bestMatch->match_time,
            'weather' => $bestMatch->weatherForecast,
        ];
    }

    /**
     * Krijg alle geschikte match datums gesorteerd op score.
     */
    public function getSuitableMatchDates(): array
    {
        return $this->matches()
            ->where('is_suitable', true)
            ->orderBy('match_score', 'desc')
            ->orderBy('match_date', 'asc')
            ->with('weatherForecast')
            ->get()
            ->map(function ($match) {
                return [
                    'date' => $match->match_date,
                    'time' => $match->match_time,
                    'score' => $match->match_score,
                    'weather' => $match->weatherForecast,
                ];
            })
            ->toArray();
    }
}
