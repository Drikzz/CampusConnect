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
      $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Foreign key user_id
      $table->string('seller_code'); // Remove the foreign key constraint
      $table->string('phone_number')->nullable();
      $table->string('account_name')->nullable();
      $table->enum('type', ['credit', 'debit'])->nullable(); // credit = refill, debit = deduction
      $table->decimal('amount', 10, 2);
      $table->decimal('previous_balance', 10, 2)->nullable(); // Nullable because no balance yet on first refill
      $table->decimal('new_balance', 10, 2)->nullable();
      $table->enum('reference_type', ['verification', 'order', 'withdrawal', 'refund', 'refill']);
      $table->string('reference_id')->nullable(); // Holds verification request ID, order ID, or payment ref #
      $table->enum('status', ['pending', 'completed', 'failed', 'rejected'])->default('pending');
      $table->text('description')->nullable(); // Remarks for the transaction

      // New verification columns
      $table->string('verification_type')->nullable(); // e.g., 'seller_activation'
      $table->json('verification_data')->nullable(); // Stores ID selfie, WMSU email, agreement status

      $table->string('receipt_path')->nullable(); // If needed for proof of transactions
      $table->timestamp('processed_at')->nullable(); // Admin approval time
      $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null'); // Admin user ID
      $table->text('remarks')->nullable(); // Admin rejection reasonwho processed

      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('wallet_transactions');
  }
};
