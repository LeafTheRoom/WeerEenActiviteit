<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Controleer of de kolom duration_minutes bestaat
        if (Schema::hasColumn('activities', 'duration_minutes')) {
            // Voeg eerst de nieuwe kolom toe
            Schema::table('activities', function (Blueprint $table) {
                $table->integer('duration_hours')->default(1)->after('max_precipitation');
            });

            // Converteer bestaande data van minuten naar uren (afgerond)
            DB::table('activities')->update([
                'duration_hours' => DB::raw('CEIL(duration_minutes / 60)')
            ]);

            // Verwijder de oude kolom
            Schema::table('activities', function (Blueprint $table) {
                $table->dropColumn('duration_minutes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Voeg duration_minutes terug toe
        Schema::table('activities', function (Blueprint $table) {
            $table->integer('duration_minutes')->default(60)->after('max_precipitation');
        });

        // Converteer bestaande data van uren naar minuten
        if (Schema::hasColumn('activities', 'duration_hours')) {
            DB::table('activities')->update([
                'duration_minutes' => DB::raw('duration_hours * 60')
            ]);

            // Verwijder de nieuwe kolom
            Schema::table('activities', function (Blueprint $table) {
                $table->dropColumn('duration_hours');
            });
        }
    }
};
