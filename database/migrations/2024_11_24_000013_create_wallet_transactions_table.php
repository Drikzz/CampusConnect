<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('wallet_transactions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key user_id
      $table->decimal('amount', 10, 2);
      $table->decimal('previous_balance', 10, 2)->nullable(); // Nullable because no balance yet on first refill
      $table->decimal('new_balance', 10, 2)->nullable();
      $table->string('reference_type'); // refill, withdraw, deduction
      $table->string('reference_id')->nullable(); // Payment Ref Number or Transaction ID
      $table->string('status')->default('pending'); // pending, completed, rejected
      $table->text('description')->nullable(); // Remarks for the transaction
      $table->string('receipt_path')->nullable(); // For uploading ref number screenshot
      $table->timestamp('processed_at')->nullable();
      $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null'); // Nullable Admin who processed
      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('wallet_transactions');
  }
};
