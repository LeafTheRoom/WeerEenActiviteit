<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Activity;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

echo "=== Test Automatische Flow ===" . PHP_EOL . PHP_EOL;

$user = User::where('email', 'testerbram123@gmail.com')->first();

echo "1. Oude test activiteiten opruimen..." . PHP_EOL;
Activity::where('user_id', $user->id)
    ->where('name', 'like', 'AUTO%')
    ->delete();

echo "2. Nieuwe activiteit aanmaken..." . PHP_EOL;
$activity = Activity::create([
    'user_id' => $user->id,
    'name' => 'AUTO-TEST ' . now()->format('H:i:s'),
    'description' => 'Automatische test voor mail notificatie',
    'location' => 'Amsterdam',
    'min_temperature' => -30,
    'max_temperature' => 60,
    'max_wind_speed' => 300,
    'max_precipitation' => 200,
    'duration_hours' => 1,
    'is_active' => true,
    'preferred_times' => ['morning'],
]);

echo "   ✓ Activiteit: {$activity->name}" . PHP_EOL . PHP_EOL;

echo "3. Weather update command uitvoeren (simuleert scheduler)..." . PHP_EOL;
Artisan::call('weather:update-matches');
$output = Artisan::output();
echo $output;

echo PHP_EOL;
echo "4. Queue checken..." . PHP_EOL;
$jobsInQueue = DB::table('jobs')->count();
echo "   Jobs in queue: {$jobsInQueue}" . PHP_EOL;

if ($jobsInQueue > 0) {
    echo PHP_EOL . "5. Queue verwerken..." . PHP_EOL;
    Artisan::call('queue:work', ['--stop-when-empty' => true, '--tries' => 1]);
    echo "   ✓ Queue verwerkt" . PHP_EOL . PHP_EOL;
    
    $failed = DB::table('failed_jobs')->count();
    if ($failed > 0) {
        echo "   ⚠️  Failed jobs: {$failed}" . PHP_EOL;
    } else {
        echo "   ✓ Geen failed jobs" . PHP_EOL . PHP_EOL;
        echo "🎉 EMAIL VERSTUURD naar testerbram123@gmail.com!" . PHP_EOL;
        echo "   Check je inbox!" . PHP_EOL;
    }
}

echo PHP_EOL;
echo "=== Hoe het automatisch werkt ===" . PHP_EOL;
echo "1. Start de server met: composer run dev" . PHP_EOL;
echo "2. De scheduler draait nu automatisch elke 5 minuten" . PHP_EOL;
echo "3. Bij nieuwe matches wordt automatisch een email verstuurd" . PHP_EOL;
echo "4. De queue worker verwerkt de emails automatisch" . PHP_EOL;
