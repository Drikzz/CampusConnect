<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class AddWalletDeductionProcessedToOrdersAndTradeTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add column to orders table if it doesn't exist
        if (!Schema::hasColumn('orders', 'wallet_deduction_processed')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->boolean('wallet_deduction_processed')->default(false)
                    ->comment('Tracks if wallet deduction has been processed');
            });
            Log::info('Added wallet_deduction_processed column to orders table');
        }

        // Add column to trade_transactions table if it doesn't exist
        if (!Schema::hasColumn('trade_transactions', 'wallet_deduction_processed')) {
            Schema::table('trade_transactions', function (Blueprint $table) {
                $table->boolean('wallet_deduction_processed')->default(false)
                    ->comment('Tracks if wallet deduction has been processed');
            });
            Log::info('Added wallet_deduction_processed column to trade_transactions table');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No need to drop columns in down() method as it could cause data loss
        // If needed, you can uncomment the following
        
        /*
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'wallet_deduction_processed')) {
                $table->dropColumn('wallet_deduction_processed');
            }
        });

        Schema::table('trade_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('trade_transactions', 'wallet_deduction_processed')) {
                $table->dropColumn('wallet_deduction_processed');
            }
        });
        */
    }
}
