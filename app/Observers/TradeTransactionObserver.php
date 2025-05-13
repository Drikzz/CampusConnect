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
            'amount' => $trade->amount
        ]);

        try {
            // Check if deduction was already processed
            if ($trade->wallet_deduction_processed) {
                Log::info('TradeObserver: Wallet deduction already processed, skipping', [
                    'trade_id' => $trade->id
                ]);
                return;
            }
            
            $wallet = $trade->seller->wallet;

            if (!$wallet) {
                Log::warning('TradeObserver: Wallet not found', [
                    'trade_id' => $trade->id,
                    'seller_code' => $trade->seller_code
                ]);
                return;
            }

            // Fetch wallet deduction rate from settings
            $deductionRate = (float) \App\Models\Setting::get('wallet_deduction_rate', 0);

            // Validate deduction rate
            if ($deductionRate < 0 || $deductionRate > 100) {
                Log::error('Invalid wallet deduction rate', ['deduction_rate' => $deductionRate]);
                return;
            }

            // Calculate deduction amount as a percentage of the transaction value
            $deductionAmount = round(($trade->amount * $deductionRate) / 100, 2);

            // Log the calculated deduction amount
            Log::info('Calculated deduction amount', [
                'trade_id' => $trade->id,
                'deduction_rate' => $deductionRate,
                'trade_amount' => $trade->amount,
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
                $deductionResult = $wallet->deductBalance($deductionAmount);
                
                if (!$deductionResult) {
                    \DB::rollBack();
                    Log::warning('TradeObserver: Wallet deduction failed', [
                        'trade_id' => $trade->id,
                        'seller_code' => $trade->seller_code,
                        'deduction_amount' => $deductionAmount
                    ]);
                    return;
                }
                
                $trade->wallet_deduction_processed = true;
                $trade->save();
                
                \DB::commit();
                
                Log::info('TradeObserver: Wallet deduction successful', [
                    'trade_id' => $trade->id,
                    'seller_code' => $trade->seller_code,
                    'deduction_amount' => $deductionAmount,
                    'wallet_balance_after' => $wallet->fresh()->balance
                ]);
            } catch (\Exception $e) {
                \DB::rollBack();
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
}
