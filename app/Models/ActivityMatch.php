<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'weather_forecast_id',
        'match_date',
        'match_time',
        'match_score',
        'is_suitable',
        'user_notified',
        'status',
    ];

    protected $casts = [
        'match_date' => 'date',
        'match_time' => 'datetime',
        'is_suitable' => 'boolean',
        'user_notified' => 'boolean',
    ];

    /**
     * Get the activity for this match.
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * Get the weather forecast for this match.
     */
    public function weatherForecast(): BelongsTo
    {
        return $this->belongsTo(WeatherForecast::class);
    }

    /**
     * Scope for pending matches.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for suitable matches.
     */
    public function scopeSuitable($query)
    {
        return $query->where('is_suitable', true);
    }

    /**
     * Scope for unnotified matches.
     */
    public function scopeUnnotified($query)
    {
        return $query->where('user_notified', false);
    }
}
