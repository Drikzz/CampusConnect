<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->string('seller_code')->constrained('seller_code')->onDelete('cascade');

            // Basic order information
            $table->string('email')->nullable();
            $table->string('phone');
            $table->decimal('sub_total', 10, 2);
            $table->string('payment_method');

            // Update meetup details to use meetup_locations table
            $table->foreignId('meetup_location_id')->nullable()->constrained('meetup_locations');
            $table->dateTime('meetup_schedule')->nullable();
            $table->text('meetup_notes')->nullable();
            $table->string('meetup_confirmation_code')->nullable(); // Add confirmation code
            $table->dateTime('meetup_confirmed_at')->nullable(); // Track when meetup was confirmed

            // Order status with all possible states
            $table->enum('status', [
                'Pending',          // Initial state when order is placed
                'Accepted',         // Seller has accepted the order
                'Meetup Scheduled', // Meetup time and place set
                'Delivered',        // Item delivered but payment in escrow
                'Completed',        // Transaction complete
                'Cancelled',        // Order cancelled
                'Disputed'          // Issue raised by buyer
            ])->default('Pending');

            // Status tracking
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();

            // If order is cancelled or disputed
            $table->string('cancellation_reason')->nullable();
            $table->string('cancelled_by')->nullable(); // buyer or seller
            
            // Flag to track if wallet deduction was processed
            $table->boolean('wallet_deduction_processed')->default(false);

            // Add indexes for frequently queried columns
            $table->index('status');
            $table->index(['seller_code', 'status']); // For filtering seller orders by status
            $table->index(['buyer_id', 'status']); // For filtering buyer orders by status
            $table->index('meetup_schedule'); // For date-based queries

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
