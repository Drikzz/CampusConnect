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
        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');          // "Grade 7", "Grade 8" etc
            $table->integer('level');        // 7,8,9,10,11,12
            $table->string('type');          // "JHS" or "SHS"
            $table->timestamps();
        });

        // Seed initial data
        DB::table('grade_levels')->insert([
            // Junior High School
            ['name' => 'Grade 7', 'level' => 7, 'type' => 'JHS', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grade 8', 'level' => 8, 'type' => 'JHS', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grade 9', 'level' => 9, 'type' => 'JHS', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grade 10', 'level' => 10, 'type' => 'JHS', 'created_at' => now(), 'updated_at' => now()],
            
            // Senior High School
            ['name' => 'Grade 11', 'level' => 11, 'type' => 'SHS', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grade 12', 'level' => 12, 'type' => 'SHS', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_levels');
    }
};
