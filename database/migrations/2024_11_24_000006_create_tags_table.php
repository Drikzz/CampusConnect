<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('tags', function (Blueprint $table) {

      $table->id();
      $table->string('name')->unique();
      $table->string('slug')->unique();
      $table->timestamps();
    });

    // Insert predefined tags
    $tags = [
      'Books',
      'Electronics',
      'School Supplies',
      'Notes',
      'Uniforms',
      'Lab Equipment',
      'Sports',
      'Art Supplies',
      'Computing',
      'Stationery'
    ];

    foreach ($tags as $name) {
      DB::table('tags')->insert([
        'name' => $name,
        'slug' => Str::slug($name),
        'created_at' => now(),
        'updated_at' => now()
      ]);
    }
  }

  public function down(): void
  {
    Schema::dropIfExists('tags');
  }
};