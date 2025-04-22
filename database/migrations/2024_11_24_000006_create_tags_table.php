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
      $table->string('description')->nullable();
      $table->timestamps();
    });

    // Insert predefined tags
    $tags = [
      'Books' => 'Academic and learning materials',
      'Electronics' => 'Electronic devices and accessories',
      'School Supplies' => 'General school supplies',
      'Notes' => 'Study notes and materials',
      'Uniforms' => 'School uniforms and attire',
      'Lab Equipment' => 'Laboratory equipment and supplies',
      'Sports' => 'Sports equipment and gear',
      'Art Supplies' => 'Art and craft materials',
      'Computing' => 'Computer hardware and software',
      'Stationery' => 'Writing and office supplies'
    ];

    foreach ($tags as $name => $description) {
      DB::table('tags')->insert([
        'name' => $name,
        'slug' => Str::slug($name),
        'description' => $description,
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
