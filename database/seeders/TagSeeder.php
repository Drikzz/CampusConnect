<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
  public function run(): void
  {
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
      Tag::create([
        'name' => $name,
        'slug' => Str::slug($name),
        'description' => $description
      ]);
    }
  }
}
