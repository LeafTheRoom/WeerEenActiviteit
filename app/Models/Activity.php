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

        // Check precipitation
        if ($forecast->precipitation > $this->max_precipitation) {
            return false;
        }

        return true;
    }

    /**
     * Calculate match score (0-100) based on how well weather fits.
     */
    public function calculateMatchScore(WeatherForecast $forecast): int
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
