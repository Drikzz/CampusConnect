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
        // create_user_types_table.php
        Schema::create('user_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. 'college', 'highschool', 'employee'
            $table->string('code')->unique(); // e.g. 'COL', 'HS', 'EMP'
            $table->timestamps();
        });

        // Seed default user types
        DB::table('user_types')->insert([
            ['name' => 'High School Student', 'code' => 'HS', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'College Student', 'code' => 'COL', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Alumni', 'code' => 'ALM', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Post Graduate', 'code' => 'PG', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Employee', 'code' => 'EMP', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_types');
    }
};
