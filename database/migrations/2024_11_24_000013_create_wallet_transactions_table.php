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
      $table->string('seller_code');
      $table->foreign('seller_code')->references('seller_code')->on('users')->onDelete('cascade');
      $table->string('type'); // credit, debit
      $table->decimal('amount', 10, 2);
      $table->decimal('previous_balance', 10, 2);
      $table->decimal('new_balance', 10, 2);
      $table->string('reference_type'); // order, withdrawal, refund
      $table->string('reference_id');
      $table->string('status')->default('pending'); // pending, completed, failed
      $table->text('description')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('wallet_transactions');
  }
};
