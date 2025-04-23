<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_balance_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('seller_wallets')->onDelete('cascade');
            $table->string('seller_code');
            $table->enum('transaction_type', ['order', 'trade']); // To track which type of transaction
            $table->string('transaction_id'); // Reference to the order or trade ID
            $table->decimal('transaction_amount', 10, 2); // Total transaction amount
            $table->decimal('deduction_rate', 5, 2); // The percentage rate applied
            $table->decimal('deduction_amount', 10, 2); // The actual amount deducted
            $table->decimal('previous_balance', 10, 2); // Wallet balance before deduction
            $table->decimal('new_balance', 10, 2); // Wallet balance after deduction
            $table->text('description')->nullable();
            $table->timestamp('processed_at');
            $table->timestamps();
            
            $table->index('seller_code');
            $table->index('transaction_type');
            $table->unique(['transaction_type', 'transaction_id']); // Ensure each transaction is only deducted once
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_balance_deductions');
    }
};
