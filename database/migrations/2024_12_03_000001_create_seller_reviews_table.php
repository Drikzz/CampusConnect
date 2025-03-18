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
        Schema::create('seller_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade'); // Buyer leaving review
            $table->string('seller_code'); // Identifies the seller
            $table->foreign('seller_code')->references('seller_code')->on('users')->onDelete('cascade'); // Links to the users table
            $table->integer('rating')->comment('Rating from 1-5 stars');
            $table->text('review')->nullable();
            $table->boolean('is_verified_purchase')->default(false)->comment('Review from verified buyer');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null'); // Ensure correct reference
            $table->timestamps();

            // Prevent multiple reviews from the same buyer for the same seller
            $table->unique(['reviewer_id', 'seller_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_reviews');
    }
};
