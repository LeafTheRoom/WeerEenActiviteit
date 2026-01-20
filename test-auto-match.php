<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Activity;
use App\Services\WeatherService;

echo "=== Test: Automatische Match & Mail ===" . PHP_EOL . PHP_EOL;

// Zoek de testuser
$user = User::where('email', 'testerbram123@gmail.com')->first();

if (!$user) {
    echo "❌ User testerbram123@gmail.com niet gevonden" . PHP_EOL;
    exit(1);
}

echo "✓ User: {$user->name} ({$user->email})" . PHP_EOL;

// Maak een nieuwe test activiteit met zeer ruime voorwaarden (zodat match gevonden wordt)
$activity = Activity::create([
    'user_id' => $user->id,
    'name' => 'Test Activiteit - ' . now()->format('H:i:s'),
    'description' => 'Test om te zien of mail wordt verstuurd bij match',
    'location' => 'Amsterdam',
    'min_temperature' => -10,  // Heel ruim
    'max_temperature' => 40,   // Heel ruim
    'max_wind_speed' => 100,   // Heel ruim
    'max_precipitation' => 50, // Heel ruim
    'duration_hours' => 2,
    'is_active' => true,
    'preferred_times' => ['morning', 'afternoon', 'evening'],
]);

echo "✓ Activiteit aangemaakt: {$activity->name}" . PHP_EOL . PHP_EOL;

// Nu de weather service runnen om matches te vinden
echo "=== Weer ophalen en matches zoeken ===" . PHP_EOL;
$weatherService = app(WeatherService::class);

try {
    $forecasts = $weatherService->fetchForecast($activity->location, 5);
    echo "✓ Weersvoorspellingen opgehaald: " . count($forecasts) . " dagen" . PHP_EOL;
    
    $matchCount = $weatherService->findActivityMatches();
    echo "✓ Matches gevonden: {$matchCount}" . PHP_EOL;
    
    if ($matchCount > 0) {
        echo PHP_EOL;
        echo "🎉 SUCCESS! Er is een match gevonden!" . PHP_EOL;
        echo "   Een notificatie is naar de queue gestuurd." . PHP_EOL;
        echo "   De queue worker verwerkt deze en stuurt een email naar:" . PHP_EOL;
        echo "   📧 {$user->email}" . PHP_EOL;
        echo PHP_EOL;
        echo "   Check je inbox over 5-10 seconden!" . PHP_EOL;
        echo "   Vergeet niet de SPAM folder te checken!" . PHP_EOL;
    } else {
        echo PHP_EOL;
        echo "❌ Geen match gevonden (geen geschikt weer)" . PHP_EOL;
        echo "   De activiteit blijft actief en wordt later opnieuw gecheckt." . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL;
echo "Tip: Bekijk de activiteit op http://localhost:8000/dashboard" . PHP_EOL;
