<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    // Create user verification table
    Schema::create('user_verifications', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
      $table->timestamp('username_changed_at')->nullable();
      $table->timestamp('phone_verified_at')->nullable();
      $table->string('phone_verification_code')->nullable();
      $table->timestamp('email_verification_code_expires_at')->nullable();
      $table->string('email_verification_code')->nullable();
      $table->boolean('is_phone_verified')->default(false);
      $table->boolean('is_email_verified')->default(false);
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('user_verifications');
  }
};
