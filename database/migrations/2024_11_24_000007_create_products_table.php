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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('discount', 8, 2)->default(0); // Change to decimal for storing 0.15 instead of 15
            $table->decimal('discounted_price', 10, 2)->nullable();
            $table->json('images')->nullable();
            $table->integer('stock');
            $table->string('seller_code');
            $table->foreign('seller_code')->references('seller_code')->on('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->boolean('is_buyable')->default(true);
            $table->boolean('is_tradable')->default(false);
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->nullable(false);
            $table->softDeletes();
            $table->json('old_attributes')->nullable();         
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
