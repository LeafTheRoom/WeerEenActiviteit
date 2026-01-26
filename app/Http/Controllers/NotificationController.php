<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $notifications = $user->notifications()->paginate(15);
        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->back()->with('success', 'Melding gemarkeerd als gelezen');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $user->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'Alle meldingen gemarkeerd als gelezen');
    }

    /**
     * Delete a notification.
     */
    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();

        return redirect()->back()->with('success', 'Melding verwijderd');
    }

    /**
     * Check for new match notifications (API endpoint).
     */
    public function checkNewMatches(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Haal ongelezen ActivityMatchFound notificaties op
        $newMatches = $user->unreadNotifications()
            ->where('type', 'App\\Notifications\\ActivityMatchFound')
            ->get();

        if ($newMatches->isEmpty()) {
            return response()->json([
                'hasNewMatches' => false,
                'matches' => []
            ]);
        }

        $matches = $newMatches->map(function ($notification) {
            $data = $notification->data;
            $matchDate = \Carbon\Carbon::parse($data['match_date']);
            $matchTime = \Carbon\Carbon::parse($data['match_time']);
            
            // Markeer als gelezen
            $notification->markAsRead();
            
            return [
                'activityName' => $data['activity_name'] ?? 'Onbekend',
                'date' => $matchDate->isoFormat('dddd D MMMM YYYY'),
                'time' => $matchTime->format('H:i') . ' - ' . $matchTime->copy()->addHours($data['duration_hours'] ?? 1)->format('H:i') . ' uur',
                'temperature' => $data['temperature'] ?? 'N/A',
                'windSpeed' => $data['wind_speed'] ?? 'N/A',
                'precipitation' => $data['precipitation'] ?? 'N/A',
            ];
        });

        return response()->json([
            'hasNewMatches' => true,
            'matches' => $matches
        ]);
    }
}
