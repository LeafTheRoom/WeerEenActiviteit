<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Activity;
use App\Models\ActivityMatch;
use App\Models\WeatherForecast;
use App\Notifications\ActivityMatchFound;

echo "=== Direct Email Test voor Activity Match ===" . PHP_EOL . PHP_EOL;

$user = User::where('email', 'testerbram123@gmail.com')->first();

if (!$user) {
    echo "❌ User niet gevonden" . PHP_EOL;
    exit(1);
}

echo "✓ User: {$user->name} ({$user->email})" . PHP_EOL;

// Zoek een bestaande match
$match = ActivityMatch::with(['activity', 'weatherForecast'])
    ->whereHas('activity', function($q) use ($user) {
        $q->where('user_id', $user->id);
    })
    ->where('is_suitable', true)
    ->latest()
    ->first();

if (!$match) {
    echo "❌ Geen match gevonden voor deze user" . PHP_EOL;
    exit(1);
}

echo "✓ Match gevonden:" . PHP_EOL;
echo "  Activiteit: {$match->activity->name}" . PHP_EOL;
echo "  Datum: {$match->match_date}" . PHP_EOL;
echo "  Tijd: {$match->match_time}" . PHP_EOL;
echo PHP_EOL;

echo "Versturen email DIRECT (zonder queue)..." . PHP_EOL;

try {
    // Directe mail zonder queue
    $notification = new ActivityMatchFound($match);
    $mailMessage = $notification->toMail($user);
    
    \Illuminate\Support\Facades\Mail::send(
        $mailMessage->markdown,
        $mailMessage->viewData,
        function($message) use ($user, $mailMessage) {
            $message->to($user->email)
                    ->subject($mailMessage->subject);
        }
    );
    
    echo "✅ Email direct verstuurd naar {$user->email}" . PHP_EOL;
    echo "   Check je inbox binnen 30 seconden!" . PHP_EOL;
    echo "   Onderwerp: {$mailMessage->subject}" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo PHP_EOL;
    echo "Stack trace:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
