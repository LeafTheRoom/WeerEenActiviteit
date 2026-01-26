<?php

namespace App\Console\Commands;

use App\Services\WeatherService;
use App\Models\Activity;
use Illuminate\Console\Command;

class UpdateWeatherAndMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:update-matches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update weather forecasts and find new activity matches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting weather update...');
        
        $weatherService = new WeatherService();
        
        // Haal alle unieke locaties op van actieve activiteiten
        $locations = Activity::where('is_active', true)
            ->distinct()
            ->pluck('location');
        
        $this->info('Found ' . $locations->count() . ' unique location(s)');
        
        // Update weather voor elke locatie
        foreach ($locations as $location) {
            $this->info('Fetching weather for: ' . $location);
            $weatherService->fetchForecast($location);
        }
        
        // Vind nieuwe matches en verstuur notificaties
        $this->info('Finding activity matches...');
        $matchResult = $weatherService->findActivityMatches();
        
        $this->info('✅ Weather update complete!');
        $this->info('📧 New notifications sent: ' . $matchResult['count']);
        
        return Command::SUCCESS;
    }
}
