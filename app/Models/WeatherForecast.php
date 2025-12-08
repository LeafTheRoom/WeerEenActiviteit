<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeatherForecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'forecast_date',
        'forecast_time',
        'location',
        'temperature',
        'wind_speed',
        'precipitation',
        'humidity',
        'condition',
        'description',
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'forecast_time' => 'datetime',
        'temperature' => 'decimal:2',
        'wind_speed' => 'decimal:2',
        'precipitation' => 'decimal:2',
    ];

    /**
     * Get the matches for this forecast.
     */
    public function matches(): HasMany
    {
        return $this->hasMany(ActivityMatch::class);
    }

    /**
     * Scope for upcoming forecasts.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('forecast_date', '>=', now()->toDateString())
            ->orderBy('forecast_date')
            ->orderBy('forecast_time');
    }

    /**
     * Scope for specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('forecast_date', $date);
    }
}
