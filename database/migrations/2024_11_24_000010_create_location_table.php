<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->timestamps();
        });

        // Insert locations with coordinates
        $locations = [
            ['name' => 'WMSU Canteen', 'latitude' => 6.912892502432668, 'longitude' => 122.06177697832719],
            ['name' => 'WMSU Library', 'latitude' => 6.912629451896356, 'longitude' => 122.06052675192008],
            ['name' => 'CTE Park', 'latitude' => 6.912622978832384, 'longitude' => 122.06106584283167],
            ['name' => 'WMSU CLAW Canteen', 'latitude' => 6.913461503920047, 'longitude' => 122.06087352835287],
            ['name' => 'Open Field', 'latitude' => 6.9132072117384675, 'longitude' => 122.06128139366133],
            ['name' => 'Open Stage', 'latitude' => 6.913503361915724, 'longitude' => 122.06129880528563],
            ['name' => 'Covered Court', 'latitude' => 6.913837227944562, 'longitude' => 122.06159582603337],
            ['name' => 'Campus B mini-canteen', 'latitude' => 6.912457153491456, 'longitude' => 122.0635060541529],
            ['name' => 'Campus B garments canteen', 'latitude' => 6.913245294685882, 'longitude' => 122.06340725691392],
            ['name' => 'CAIS Seating Area', 'latitude' => 6.912221735743497, 'longitude' => 122.06342468448292],
            ['name' => 'Campus A Gate', 'latitude' => 6.912719986426812, 'longitude' => 122.06175988294497],
            ['name' => 'Campus B Gate', 'latitude' => 6.913349602798584, 'longitude' => 122.0625666213794]
        ];

        foreach ($locations as $location) {
            DB::table('locations')->insert(array_merge($location, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
