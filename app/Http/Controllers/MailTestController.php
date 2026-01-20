<?php

namespace App\Http\Controllers;

use App\Models\ActivityMatch;
use App\Notifications\ActivityMatchFound;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailTestController extends Controller
{
    public function preview()
    {
        $match = ActivityMatch::with(['activity', 'weatherForecast'])
            ->first();

        if (!$match) {
            return 'Geen activity match gevonden om te previwen';
        }

        $notification = new ActivityMatchFound($match);
        $mailMessage = $notification->toMail($match->activity->user);

        return view('mail-preview', [
            'subject' => $mailMessage->subject,
            'greeting' => $mailMessage->greeting,
            'introLines' => $mailMessage->introLines,
            'outroLines' => $mailMessage->outroLines,
            'actionText' => $mailMessage->actionText,
            'actionUrl' => $mailMessage->actionUrl,
        ]);
    }

    public function send(Request $request)
    {
        $user = $request->user();
        
        $match = ActivityMatch::where('user_notified', false)
            ->whereHas('activity', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->first();

        if (!$match) {
            return back()->with('error', 'Geen onverwerkte matches gevonden');
        }

        $user->notify(new ActivityMatchFound($match));

        return back()->with('success', 'Test mail verstuurd! Check je email.');
    }
}

