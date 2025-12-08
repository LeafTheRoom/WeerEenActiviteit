<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('weather_forecasts', function (Blueprint $table) {
            $table->id();
            $table->date('forecast_date');
            $table->time('forecast_time');
            $table->string('location')->default('Nederland'); // Locatie
            
            // Weergegevens
            $table->decimal('temperature', 5, 2); // Temperatuur in °C
            $table->decimal('wind_speed', 5, 2); // Windsnelheid in km/h
            $table->decimal('precipitation', 5, 2)->default(0); // Neerslag in mm
            $table->integer('humidity')->nullable(); // Luchtvochtigheid in %
            $table->string('condition')->nullable(); // bijv. "sunny", "rainy", "cloudy"
            $table->text('description')->nullable();
            
            $table->timestamps();
            
            // Index voor snelle datum-gebaseerde queries
            $table->index(['forecast_date', 'forecast_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_forecasts');
    }
};
