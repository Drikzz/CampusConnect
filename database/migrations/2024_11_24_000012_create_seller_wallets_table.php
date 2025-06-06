<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('seller_wallets', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key user_id
      $table->string('seller_code')->unique(); // Just for display purposes
      $table->decimal('balance', 10, 2)->default(0.00);
      $table->boolean('is_activated')->default(false); // Verification step
      $table->enum('status', ['pending', 'pending_approval', 'active', 'suspended', 'rejected'])->default('pending');
      $table->timestamp('activated_at')->nullable();
      $table->boolean('wallet_deduction_processed')->default(false)->comment('Tracks if wallet deduction has been processed'); // Track wallet deductions
      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('seller_wallets');
  }
};
