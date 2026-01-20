<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SafeMailChannel extends MailChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        try {
            parent::send($notifiable, $notification);
            
            Log::info('Email notification sent successfully', [
                'notification' => get_class($notification),
                'recipient' => $notifiable->email ?? 'unknown'
            ]);
            
        } catch (\Exception $e) {
            // Log the error maar laat de applicatie doorlopen
            Log::warning('Email notification failed, notification saved to database only', [
                'notification' => get_class($notification),
                'recipient' => $notifiable->email ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            
            // Email fout wordt niet verder gegooid, zodat database notificatie wel opgeslagen wordt
        }
    }
}
