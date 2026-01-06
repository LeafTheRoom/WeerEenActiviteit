<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenWeatherMap API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the OpenWeatherMap API integration.
    | Get your free API key at: https://openweathermap.org/api
    |
    */

    'api_key' => env('WEATHER_API_KEY', ''),
    
    'api_url' => env('WEATHER_API_URL', 'https://api.openweathermap.org/data/2.5'),
    
    'default_location' => env('WEATHER_DEFAULT_LOCATION', 'Amsterdam'),
    
    'default_country' => env('WEATHER_DEFAULT_COUNTRY', 'NL'),
    
    /*
    |--------------------------------------------------------------------------
    | Forecast Settings
    |--------------------------------------------------------------------------
    */
    
    'forecast_days' => 5, // Free tier allows 5 days
    
    'units' => 'metric', // metric (Celsius), imperial (Fahrenheit), standard (Kelvin)
    
    'language' => 'nl', // Dutch
    
    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    
    'cache_enabled' => true,
    
    'cache_duration' => 1800, // 30 minutes in seconds
];
