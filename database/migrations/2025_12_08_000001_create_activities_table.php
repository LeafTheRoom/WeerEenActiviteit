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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // bijv. "Fietsen", "Wandelen", "Zeilen"
            $table->text('description')->nullable();
            
            // Weer eisen
            $table->decimal('min_temperature', 5, 2)->nullable(); // Minimale temperatuur in °C
            $table->decimal('max_temperature', 5, 2)->nullable(); // Maximale temperatuur in °C
            $table->decimal('max_wind_speed', 5, 2)->nullable(); // Maximale windsnelheid in km/h
            $table->decimal('max_precipitation', 5, 2)->default(0); // Maximale neerslag in mm
            $table->integer('duration_minutes')->default(60); // Duur van de activiteit in minuten
            
            // Extra voorkeuren
            $table->boolean('is_active')->default(true);
            $table->json('preferred_times')->nullable(); // Voorkeurstijden bijv. ["morning", "afternoon"]
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
