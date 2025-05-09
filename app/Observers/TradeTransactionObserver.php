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
            'product_id' => $trade->seller_product_id
        ]);

        try {
            // Process wallet deduction
            $deductionController = new WalletBalanceDeductionController();
            $result = $deductionController->processTradeDeduction($trade);
            
            // Add a flag indicating we've processed this trade
            if ($result['success']) {
                $trade->wallet_deduction_processed = true;
                $trade->save();
            }
            
            Log::info('TradeObserver: Deduction result', [
                'trade_id' => $trade->id,
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? 'No message',
                'transaction_id' => $result['transaction']['id'] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('TradeObserver: Error processing deduction', [
                'trade_id' => $trade->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
