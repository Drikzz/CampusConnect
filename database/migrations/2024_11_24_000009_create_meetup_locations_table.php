<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('meetup_locations', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->foreignId('location_id')->constrained()->onDelete('cascade');
      $table->string('full_name');
      $table->string('phone');
      $table->text('description')->nullable();
      $table->string('custom_location')->nullable();
      $table->decimal('latitude', 10, 8)->nullable();
      $table->decimal('longitude', 11, 8)->nullable();
      $table->boolean('is_active')->default(true);
      $table->boolean('is_default')->default(false);
      $table->time('available_from')->nullable(); // Operating hours start
      $table->time('available_until')->nullable(); // Operating hours end
      $table->json('available_days')->nullable(); // Store days when meetups are possible
      $table->integer('max_daily_meetups')->default(5); // Limit meetups per day
      $table->timestamps();

      // Add index for faster queries
      $table->index(['user_id', 'is_active']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('meetup_locations');
  }
};
