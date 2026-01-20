<?php

namespace App\Notifications;

use App\Models\ActivityMatch;
use App\Mail\MyAppMail;
use App\Notifications\Channels\SafeMailChannel;
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
        // Altijd database, en probeer mail via safe channel
        return [SafeMailChannel::class, 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        try {
            $activity = $this->match->activity;
            $weather = $this->match->weatherForecast;
            $matchDate = Carbon::parse($this->match->match_date);
            $matchTime = Carbon::parse($this->match->match_time);
            $endTime = $matchTime->copy()->addHours($activity->duration_hours);

            return (new MailMessage)
                ->subject('Geschikte dag voor: ' . $activity->name)
                ->markdown('emails.activity-match', [
                    'userName' => $notifiable->name,
                    'activityName' => $activity->name,
                    'matchDate' => $matchDate->isoFormat('dddd D MMMM YYYY'),
                    'matchTime' => $matchTime->format('H:i'),
                    'endTime' => $endTime->format('H:i'),
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
        } catch (\Exception $e) {
            \Log::error('Failed to generate email notification', [
                'error' => $e->getMessage(),
                'match_id' => $this->match->id
            ]);
            
            // Fallback naar simpele mail
            return (new MailMessage)
                ->subject('Nieuwe activiteit match gevonden')
                ->line('Er is een geschikte dag gevonden voor je activiteit.')
                ->action('Bekijk Details', url('/dashboard'))
                ->line('Dank je wel!');
        }
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
