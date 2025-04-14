<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trade_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users');
            $table->foreignId('seller_id')->nullable()->constrained('users');  // Make nullable
            $table->string('seller_code')->nullable();  // Add seller_code field
            $table->foreignId('seller_product_id')->constrained('products');
            $table->foreignId('meetup_location_id')->nullable()->constrained('meetup_locations')->nullOnDelete();  // Add meetup location reference
            $table->decimal('additional_cash', 10, 2)->default(0);
            $table->enum('status', ['pending', 'accepted', 'rejected', 'canceled', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('meetup_schedule')->nullable();  // Add scheduled meetup time
            $table->timestamps();
            $table->softDeletes();
            // Add foreign key constraint for buyer_id
            
            // Add index on seller_code for performance
            $table->index('seller_code');
        });

        Schema::create('trade_offered_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_transaction_id')->constrained('trade_transactions')->onDelete('cascade');
            $table->string('name');
            $table->integer('quantity')->default(1);
            $table->decimal('estimated_value', 10, 2);
            $table->json('images')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('trade_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_transaction_id')->constrained('trade_transactions')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users'); // Buyer or Seller
            $table->text('message');
            $table->timestamp('read_at')->nullable(); // To track if the message is read
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trade_messages');
        Schema::dropIfExists('trade_offered_items');
        Schema::dropIfExists('trade_transactions');
    }
};
