<?php

namespace App\Observers;

use App\Models\TradeTransaction;
use App\Http\Controllers\WalletBalanceDeductionController;
use Illuminate\Support\Facades\Log;

class TradeTransactionObserver
{
    /**
     * Handle the TradeTransaction "created" event.
     *
     * @param  \App\Models\TradeTransaction  $trade
     * @return void
     */
    public function created(TradeTransaction $trade)
    {
        // Check if trade was created with completed status (edge case)
        if ($trade->status === 'completed') {
            $this->handleCompletedTrade($trade);
        }
    }

    /**
     * Handle the TradeTransaction "updated" event.
     *
     * @param  \App\Models\TradeTransaction  $trade
     * @return void
     */
    public function updated(TradeTransaction $trade)
    {
        // Enhanced logging to check if the observer is firing
        Log::info('TradeObserver: Trade updated', [
            'trade_id' => $trade->id,
            'old_status' => $trade->getOriginal('status'),
            'new_status' => $trade->status,
            'is_dirty' => $trade->isDirty('status'),
            'wallet_processed' => $trade->wallet_deduction_processed
        ]);

        // Check if status was changed to completed (note lowercase in trade status)
        if ($trade->isDirty('status') && $trade->status === 'completed') {
            $this->handleCompletedTrade($trade);
        }
    }

    /**
     * Handle the TradeTransaction "saved" event (fires for both create and update).
     *
     * @param  \App\Models\TradeTransaction  $trade
     * @return void
     */
    public function saved(TradeTransaction $trade)
    {
        // This catches direct database changes that might not trigger updated/created
        if ($trade->status === 'completed' && !$trade->wallet_deduction_processed) {
            $this->handleCompletedTrade($trade);
        }
    }

    /**
     * Common handler for completed trades
     * 
     * @param  \App\Models\TradeTransaction  $trade
     * @return void
     */
    private function handleCompletedTrade(TradeTransaction $trade)
    {
        Log::info('TradeObserver: Trade completed, processing wallet deduction', [
            'trade_id' => $trade->id,
            'seller_code' => $trade->seller_code,
            'seller_id' => $trade->seller_id,
            'wallet_processed' => $trade->wallet_deduction_processed
        ]);

        try {
            // Check if deduction was already processed
            if ($trade->wallet_deduction_processed) {
                Log::info('TradeObserver: Wallet deduction already processed, skipping', [
                    'trade_id' => $trade->id
                ]);
                return;
            }
            
            // Check if the seller relationship is loaded
            if (!$trade->seller) {
                Log::error('TradeObserver: Seller relationship not loaded', [
                    'trade_id' => $trade->id,
                    'seller_code' => $trade->seller_code,
                    'seller_id' => $trade->seller_id
                ]);
                return;
            }
            
            $wallet = $trade->seller->wallet;

            if (!$wallet) {
                Log::error('TradeObserver: Wallet not found for seller', [
                    'trade_id' => $trade->id,
                    'seller_code' => $trade->seller_code,
                    'seller_id' => $trade->seller_id,
                    'seller_name' => $trade->seller->name ?? 'unknown'
                ]);
                return;
            }

            // Get the seller product and ensure it exists
            $sellerProduct = $trade->sellerProduct;
            if (!$sellerProduct) {
                Log::error('TradeObserver: Seller product not found', [
                    'trade_id' => $trade->id,
                    'seller_product_id' => $trade->seller_product_id
                ]);
                return;
            }

            // Get the product price 
            $productPrice = $sellerProduct->price;
            if (!is_numeric($productPrice) || $productPrice <= 0) {
                Log::error('TradeObserver: Invalid product price', [
                    'trade_id' => $trade->id,
                    'product_id' => $sellerProduct->id,
                    'price' => $productPrice
                ]);
                return;
            }

            // Fetch wallet deduction rate from settings
            $deductionRate = (float) \App\Models\Setting::get('wallet_deduction_rate', 0);
            
            // Log the settings retrieval
            Log::info('TradeObserver: Retrieved wallet deduction rate', [
                'deduction_rate' => $deductionRate
            ]);

            // Validate deduction rate
            if ($deductionRate <= 0 || $deductionRate > 100) {
                Log::error('TradeObserver: Invalid wallet deduction rate', [
                    'deduction_rate' => $deductionRate
                ]);
                return;
            }

            // Calculate deduction amount as a percentage of the product price only
            $deductionAmount = round(($productPrice * $deductionRate) / 100, 2);

            // Log the calculated deduction amount
            Log::info('TradeObserver: Calculated deduction amount', [
                'trade_id' => $trade->id,
                'deduction_rate' => $deductionRate,
                'product_price' => $productPrice,
                'deduction_amount' => $deductionAmount,
                'wallet_balance_before' => $wallet->balance
            ]);

            // Check if the wallet has sufficient balance
            if ($wallet->balance < $deductionAmount) {
                Log::warning('TradeObserver: Insufficient wallet balance for deduction', [
                    'trade_id' => $trade->id,
                    'seller_code' => $trade->seller_code,
                    'wallet_balance' => $wallet->balance,
                    'deduction_amount' => $deductionAmount
                ]);
                return;
            }

            // Perform deduction in a database transaction
            \DB::beginTransaction();
            try {
                // Call deductBalance and check the result
                $previousBalance = $wallet->balance;
                $deductionResult = $wallet->deductBalance($deductionAmount);
                
                Log::info('TradeObserver: Deduction result', [
                    'result' => $deductionResult,
                    'wallet_balance_after' => $wallet->fresh()->balance
                ]);
                
                if (!$deductionResult) {
                    \DB::rollBack();
                    Log::error('TradeObserver: Wallet deduction failed', [
                        'trade_id' => $trade->id,
                        'seller_code' => $trade->seller_code,
                        'deduction_amount' => $deductionAmount
                    ]);
                    return;
                }
                
                // Mark the transaction as processed
                $trade->wallet_deduction_processed = true;
                $saveResult = $trade->save();
                
                Log::info('TradeObserver: Marked transaction as processed', [
                    'save_result' => $saveResult,
                    'wallet_processed' => $trade->wallet_deduction_processed
                ]);
                
                // Record the transaction in wallet_transactions table
                $this->recordWalletTransaction($trade, $wallet, $deductionAmount, $productPrice, $previousBalance);
                
                \DB::commit();
                
                Log::info('TradeObserver: Wallet deduction successful', [
                    'trade_id' => $trade->id,
                    'seller_code' => $trade->seller_code,
                    'deduction_amount' => $deductionAmount,
                    'wallet_balance_after' => $wallet->fresh()->balance
                ]);
            } catch (\Exception $e) {
                \DB::rollBack();
                Log::error('TradeObserver: Exception during deduction transaction', [
                    'trade_id' => $trade->id,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('TradeObserver: Error processing wallet deduction', [
                'trade_id' => $trade->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Record the wallet transaction for the deduction
     *
     * @param  \App\Models\TradeTransaction  $trade
     * @param  \App\Models\SellerWallet  $wallet
     * @param  float  $deductionAmount
     * @param  float  $productPrice
     * @param  float  $previousBalance
     * @return void
     */
    private function recordWalletTransaction($trade, $wallet, $deductionAmount, $productPrice, $previousBalance)
    {
        try {
            \App\Models\WalletTransaction::create([
                'user_id' => $trade->seller_id,
                'seller_code' => $trade->seller_code,
                'type' => 'deduction',
                'amount' => $deductionAmount,
                'previous_balance' => $previousBalance,
                'new_balance' => $wallet->balance,
                'reference_type' => 'trade',
                'reference_id' => $trade->id,
                'status' => 'completed',
                'description' => "Fee deducted for product trade (Price: ₱" . number_format($productPrice, 2) . ")",
                'processed_at' => now(),
            ]);
            
            Log::info('TradeObserver: Wallet transaction record created', [
                'trade_id' => $trade->id,
                'seller_code' => $trade->seller_code,
                'amount' => $deductionAmount,
                'previous_balance' => $previousBalance,
                'new_balance' => $wallet->balance
            ]);
        } catch (\Exception $e) {
            Log::error('TradeObserver: Failed to create wallet transaction record', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
