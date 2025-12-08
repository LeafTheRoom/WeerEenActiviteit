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
        Schema::create('activity_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->onDelete('cascade');
            $table->foreignId('weather_forecast_id')->constrained()->onDelete('cascade');
            $table->date('match_date');
            $table->time('match_time');
            
            // Match score en status
            $table->integer('match_score')->default(0); // 0-100 hoe goed het weer past
            $table->boolean('is_suitable')->default(false); // Of het weer geschikt is
            $table->boolean('user_notified')->default(false); // Of gebruiker een melding heeft gehad
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            
            $table->timestamps();
            
            // Voorkom dubbele matches
            $table->unique(['activity_id', 'weather_forecast_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_matches');
    }
};
