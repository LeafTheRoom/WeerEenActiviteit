<?php

namespace App\Mail;

use App\Models\ActivityMatch;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MyAppMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public ActivityMatch $match,
        public string $userName
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Geschikte dag voor: ' . $this->match->activity->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $activity = $this->match->activity;
        $weather = $this->match->weatherForecast;
        $matchDate = Carbon::parse($this->match->match_date);
        $matchTime = Carbon::parse($this->match->match_time);

        return new Content(
            view: 'emails.activity-match',
            with: [
                'userName' => $this->userName,
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
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
