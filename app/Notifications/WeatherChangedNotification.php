<?php

namespace App\Notifications;

use App\Models\Activity;
use App\Models\ActivityMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class WeatherChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Activity $activity,
        public string $changeReason
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
        return (new MailMessage)
            ->subject('⚠️ Weersverandering voor: ' . $this->activity->name)
            ->greeting('Hallo ' . $notifiable->name . '!')
            ->line('Het weer is veranderd voor je geplande activiteit **' . $this->activity->name . '**.')
            ->line('')
            ->line('**Reden van verandering:**')
            ->line($this->changeReason)
            ->line('')
            ->action('Bekijk nieuwe matches', url('/dashboard'))
            ->line('We blijven op zoek naar een geschikte dag voor je!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'weather_changed',
            'activity_id' => $this->activity->id,
            'activity_name' => $this->activity->name,
            'change_reason' => $this->changeReason,
            'message' => 'Het weer is veranderd voor ' . $this->activity->name,
        ];
    }
}
