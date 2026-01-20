<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\ActivityMatch;
use App\Notifications\ActivityMatchFound;

echo "=== Test Notificatie Systeem ===" . PHP_EOL . PHP_EOL;

$user = User::where('email', 'testerbram123@gmail.com')->first();

if (!$user) {
    echo "❌ User niet gevonden met email testerbram123@gmail.com" . PHP_EOL;
    echo "Maak eerst een account aan met dit email adres" . PHP_EOL;
    exit(1);
}

echo "✓ User gevonden: {$user->name} ({$user->email})" . PHP_EOL;
echo "  Activiteiten van deze user: " . $user->activities()->count() . PHP_EOL;

$match = ActivityMatch::with(['activity', 'weatherForecast'])
    ->whereHas('activity', function($q) use ($user) {
        $q->where('user_id', $user->id);
    })
    ->where('is_suitable', true)
    ->first();

if (!$match) {
    echo "❌ Geen geschikte match gevonden voor activiteiten van deze user" . PHP_EOL;
    echo PHP_EOL;
    echo "Test met een andere match (van een andere user):" . PHP_EOL;
    
    $match = ActivityMatch::with(['activity', 'weatherForecast'])
        ->where('is_suitable', true)
        ->first();
    
    if ($match) {
        echo "✓ Test match gevonden: {$match->activity->name}" . PHP_EOL;
    } else {
        echo "❌ Helemaal geen matches gevonden in de database" . PHP_EOL;
        exit(1);
    }
}

echo PHP_EOL;
echo "=== Versturen notificatie ===" . PHP_EOL;
echo "Match: {$match->activity->name}" . PHP_EOL;
echo "Datum: {$match->match_date}" . PHP_EOL;
echo "Naar: {$user->email}" . PHP_EOL;
echo PHP_EOL;

try {
    $user->notify(new ActivityMatchFound($match));
    echo "✓ Notificatie succesvol in queue geplaatst!" . PHP_EOL;
    echo "  De queue worker verwerkt deze en stuurt de email." . PHP_EOL;
    echo "  Check je inbox op testerbram123@gmail.com over 5-10 seconden!" . PHP_EOL;
    echo "  Vergeet niet je SPAM folder te checken!" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL;
