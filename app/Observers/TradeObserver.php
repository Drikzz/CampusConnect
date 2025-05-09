<?php

namespace App\Observers;

use App\Models\TradeTransaction;
use App\Http\Controllers\WalletBalanceDeductionController;
use Illuminate\Support\Facades\Log;

class TradeObserver
{
    /**
     * Handle the TradeTransaction "updated" event.
     *
     * @param  \App\Models\TradeTransaction  $trade
     * @return void
     */
    public function updated(TradeTransaction $trade)
    {
        // Check if status was changed to completed
        if ($trade->isDirty('status') && $trade->status === 'completed') {
            Log::info('Trade marked as completed, applying wallet deduction', [
                'trade_id' => $trade->id,
                'seller_code' => $trade->seller_code
            ]);
            
            // Process wallet deduction
            $deductionController = new WalletBalanceDeductionController();
            $result = $deductionController->processTradeDeduction($trade);
            
            Log::info('Trade deduction result', [
                'trade_id' => $trade->id, 
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        }
    }
}
