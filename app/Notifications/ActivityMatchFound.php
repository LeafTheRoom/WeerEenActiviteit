<?php

namespace App\Notifications;

use App\Models\ActivityMatch;
use App\Mail\MyAppMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class ActivityMatchFound extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ActivityMatch $match
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $activity = $this->match->activity;
        $weather = $this->match->weatherForecast;
        $matchDate = Carbon::parse($this->match->match_date);
        $matchTime = Carbon::parse($this->match->match_time);

        return (new MailMessage)
            ->subject('Geschikte dag voor: ' . $activity->name)
            ->markdown('emails.activity-match', [
                'userName' => $notifiable->name,
                'activityName' => $activity->name,
                'matchDate' => $matchDate->isoFormat('dddd D MMMM YYYY'),
                'matchTime' => $matchTime->format('H:i'),
                'location' => $activity->location,
                'duration' => $activity->duration_hours,
                
                // Wensen van de gebruiker
                'minTemperature' => $activity->min_temperature,
                'maxTemperature' => $activity->max_temperature,
                'maxWindSpeed' => $activity->max_wind_speed,
                'maxPrecipitation' => $activity->max_precipitation,
                'preferredTimes' => $activity->preferred_times ?? [],
                
                // Weersverwachting
                'weatherTemperature' => $weather->temperature,
                'weatherWindSpeed' => $weather->wind_speed,
                'weatherPrecipitation' => $weather->precipitation,
                'weatherDescription' => $weather->description,
                
                'dashboardUrl' => url('/dashboard'),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $activity = $this->match->activity;
        $date = Carbon::parse($this->match->match_date);
        
        return [
            'type' => 'activity_match',
            'activity_id' => $activity->id,
            'activity_name' => $activity->name,
            'match_id' => $this->match->id,
            'match_date' => $this->match->match_date,
            'match_time' => $this->match->match_time,
            'match_score' => $this->match->match_score,
            'location' => $activity->location,
            'message' => 'Perfecte dag gevonden voor ' . $activity->name . ' op ' . $date->isoFormat('D MMMM'),
        ];
    }
}
