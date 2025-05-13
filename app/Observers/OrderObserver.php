<?php

namespace App\Observers;

use App\Models\Order;
use App\Http\Controllers\WalletBalanceDeductionController;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function created(Order $order)
    {
        // Check if order was created with Completed status (edge case)
        if ($order->status === 'Completed') {
            $this->handleCompletedOrder($order);
        }
    }

    /**
     * Handle the Order "updated" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function updated(Order $order)
    {
        // Check if status was changed to Completed
        if ($order->isDirty('status') && $order->status === 'Completed') {
            $this->handleCompletedOrder($order);
        }
    }

    /**
     * Handle the Order "saved" event (fires for both create and update).
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function saved(Order $order)
    {
        // This catches direct database changes that might not trigger updated/created
        if ($order->status === 'Completed' && !$order->wallet_deduction_processed) {
            $this->handleCompletedOrder($order);
        }
    }

    /**
     * Common handler for completed orders
     * 
     * @param  \App\Models\Order  $order
     * @return void
     */
    private function handleCompletedOrder(Order $order)
    {
        Log::info('OrderObserver: Order completed, processing wallet deduction', [
            'order_id' => $order->id,
            'seller_code' => $order->seller_code,
            'sub_total' => $order->sub_total
        ]);

        try {
            // Check if deduction was already processed
            if ($order->wallet_deduction_processed) {
                Log::info('OrderObserver: Wallet deduction already processed, skipping', [
                    'order_id' => $order->id
                ]);
                return;
            }
            
            $wallet = $order->seller->wallet;

            if (!$wallet) {
                Log::warning('OrderObserver: Wallet not found', [
                    'order_id' => $order->id,
                    'seller_code' => $order->seller_code
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
            $deductionAmount = round(($order->sub_total * $deductionRate) / 100, 2);

            // Log the calculated deduction amount
            Log::info('Calculated deduction amount', [
                'order_id' => $order->id,
                'deduction_rate' => $deductionRate,
                'sub_total' => $order->sub_total,
                'deduction_amount' => $deductionAmount,
                'wallet_balance_before' => $wallet->balance
            ]);

            // Check if the wallet has sufficient balance
            if ($wallet->balance < $deductionAmount) {
                Log::warning('OrderObserver: Insufficient wallet balance for deduction', [
                    'order_id' => $order->id,
                    'seller_code' => $order->seller_code,
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
                    Log::warning('OrderObserver: Wallet deduction failed', [
                        'order_id' => $order->id,
                        'seller_code' => $order->seller_code,
                        'deduction_amount' => $deductionAmount
                    ]);
                    return;
                }
                
                $order->wallet_deduction_processed = true;
                $order->save();
                
                \DB::commit();
                
                Log::info('OrderObserver: Wallet deduction successful', [
                    'order_id' => $order->id,
                    'seller_code' => $order->seller_code,
                    'deduction_amount' => $deductionAmount,
                    'wallet_balance_after' => $wallet->fresh()->balance
                ]);
            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('OrderObserver: Error processing wallet deduction', [
                'order_id' => $order->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
