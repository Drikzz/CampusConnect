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
            // Process wallet deduction
            $deductionController = new WalletBalanceDeductionController();
            $result = $deductionController->processOrderDeduction($order);
            
            // Add a flag indicating we've processed this order
            if ($result['success']) {
                $order->wallet_deduction_processed = true;
                $order->save();
            }
            
            Log::info('OrderObserver: Deduction result', [
                'order_id' => $order->id,
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? 'No message',
                'transaction_id' => $result['transaction']['id'] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('OrderObserver: Error processing deduction', [
                'order_id' => $order->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
