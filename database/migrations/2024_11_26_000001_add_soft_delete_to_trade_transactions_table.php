<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This adds a deleted_at nullable timestamp column to the trade_transactions table
     * which will be used by Laravel's SoftDeletes trait to track when a record is "deleted".
     * When using soft deletes, records are not actually removed from the database,
     * but instead the deleted_at column is set to the current timestamp.
     */
    public function up(): void
    {
        Schema::table('trade_transactions', function (Blueprint $table) {
            // This adds a `deleted_at` nullable timestamp column
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     * 
     * This removes the deleted_at column from the trade_transactions table.
     */
    public function down(): void
    {
        Schema::table('trade_transactions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
