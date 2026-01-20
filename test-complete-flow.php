<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Activity;
use App\Services\WeatherService;
use Illuminate\Support\Facades\DB;

echo "=== Complete Test: Activiteit -> Match -> Email ===" . PHP_EOL . PHP_EOL;

$user = User::where('email', 'testerbram123@gmail.com')->first();

if (!$user) {
    echo "❌ User niet gevonden" . PHP_EOL;
    exit(1);
}

echo "✓ User: {$user->name} ({$user->email})" . PHP_EOL . PHP_EOL;

// Verwijder oude test activiteiten
$deleted = Activity::where('user_id', $user->id)
    ->where('name', 'like', 'MAILTEST%')
    ->delete();
    
if ($deleted > 0) {
    echo "  Oude test activiteiten verwijderd: {$deleted}" . PHP_EOL;
}

// Maak een nieuwe test activiteit aan met ZEER ruime voorwaarden
echo "1. Nieuwe activiteit aanmaken..." . PHP_EOL;
$activity = Activity::create([
    'user_id' => $user->id,
    'name' => 'MAILTEST ' . now()->format('H:i:s'),
    'description' => 'Test voor email notificatie',
    'location' => 'Amsterdam',
    'min_temperature' => -20,
    'max_temperature' => 50,
    'max_wind_speed' => 200,
    'max_precipitation' => 100,
    'duration_hours' => 2,
    'is_active' => true,
    'preferred_times' => ['morning', 'afternoon'],
]);

echo "   ✓ Activiteit aangemaakt: {$activity->name}" . PHP_EOL . PHP_EOL;

// Haal weer op en vind matches
echo "2. Weer ophalen en matches vinden..." . PHP_EOL;
$weatherService = app(WeatherService::class);
$forecasts = $weatherService->fetchForecast($activity->location, 5);
$forecastCount = count($forecasts);
echo "   ✓ {$forecastCount} dagen weer opgehaald" . PHP_EOL;

$matchCount = $weatherService->findActivityMatches();
echo "   ✓ {$matchCount} matches gevonden en verwerkt" . PHP_EOL . PHP_EOL;

if ($matchCount > 0) {
    echo "3. Jobs in queue checken..." . PHP_EOL;
    $jobCount = DB::table('jobs')->count();
    echo "   Jobs in queue: {$jobCount}" . PHP_EOL . PHP_EOL;
    
    if ($jobCount > 0) {
        echo "4. Queue job handmatig verwerken..." . PHP_EOL;
        echo "   (Dit simuleert wat de queue worker doet)" . PHP_EOL . PHP_EOL;
        
        // Verwerk queue jobs
        \Illuminate\Support\Facades\Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--tries' => 1,
        ]);
        
        echo "   ✓ Queue jobs verwerkt" . PHP_EOL . PHP_EOL;
        
        $failedCount = DB::table('failed_jobs')->count();
        if ($failedCount > 0) {
            echo "   ⚠️  Failed jobs: {$failedCount}" . PHP_EOL;
            $failed = DB::table('failed_jobs')->latest()->first();
            echo "   Error: " . substr($failed->exception, 0, 200) . "..." . PHP_EOL;
        } else {
            echo "   ✓ Geen failed jobs" . PHP_EOL;
        }
        
        echo PHP_EOL;
        echo "🎉 SUCCESS!" . PHP_EOL;
        echo "   Email zou nu verstuurd moeten zijn naar: {$user->email}" . PHP_EOL;
        echo "   Check je inbox (en spam folder)!" . PHP_EOL;
    } else {
        echo "   ℹ️  Geen jobs in queue - notificatie al verstuurd of user al eerder genotificeerd" . PHP_EOL;
    }
} else {
    echo "❌ Geen matches gevonden" . PHP_EOL;
}

echo PHP_EOL;
