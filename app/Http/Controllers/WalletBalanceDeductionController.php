<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use App\Models\SellerWallet;
use App\Models\TradeTransaction;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletBalanceDeductionController extends Controller
{
    /**
     * Process deduction for a completed order
     *
     * @param int|Order $order Order ID or Order instance
     * @return array
     */
    public function processOrderDeduction($order)
    {
        // Get order by ID if needed
        if (!is_object($order)) {
            $order = Order::find($order);
        }

        // Validate order
        if (!$order) {
            Log::error('Order not found for deduction');
            return ['success' => false, 'message' => 'Order not found'];
        }

        // Check if order is completed
        if ($order->status !== 'Completed') {
            Log::info('Order is not completed, skipping deduction', ['order_id' => $order->id, 'status' => $order->status]);
            return ['success' => false, 'message' => 'Order is not completed'];
        }

        // Process the deduction
        Log::info('Processing order deduction', ['order_id' => $order->id, 'amount' => $order->sub_total]);
        
        // Check if deduction already exists
        if ($this->deductionExists('order', $order->id)) {
            Log::info('Deduction already exists for this order', ['order_id' => $order->id]);
            return ['success' => true, 'message' => 'Deduction was already applied', 'transaction' => ['id' => 'existing']];
        }
        
        return $this->processDeduction(
            $order->seller_code,
            'order',
            $order->id,
            $order->sub_total
        );
    }

    /**
     * Process deduction for a completed trade
     *
     * @param int|TradeTransaction $trade Trade ID or TradeTransaction instance
     * @return array
     */
    public function processTradeDeduction($trade)
    {
        // Get trade by ID if needed
        if (!is_object($trade)) {
            $trade = TradeTransaction::find($trade);
        }

        // Validate trade
        if (!$trade) {
            Log::error('Trade not found for deduction');
            return ['success' => false, 'message' => 'Trade not found'];
        }

        // Check if trade is completed
        if ($trade->status !== 'completed') {
            Log::info('Trade is not completed, skipping deduction', ['trade_id' => $trade->id, 'status' => $trade->status]);
            return ['success' => false, 'message' => 'Trade is not completed'];
        }

        // For trades, calculate the value based on the seller product price
        $tradeAmount = 0;
        
        // Get the product details
        if ($trade->seller_product_id) {
            $product = \App\Models\Product::find($trade->seller_product_id);
            if ($product) {
                $tradeAmount = $product->price;
                
                // Add any additional cash if applicable
                if ($trade->additional_cash > 0) {
                    $tradeAmount += $trade->additional_cash;
                }
            }
        }
        
        Log::info('Processing trade deduction', [
            'trade_id' => $trade->id, 
            'product_price' => $tradeAmount - ($trade->additional_cash ?? 0),
            'additional_cash' => $trade->additional_cash ?? 0,
            'total_amount' => $tradeAmount
        ]);

        // Check if deduction already exists
        if ($this->deductionExists('trade', $trade->id)) {
            Log::info('Deduction already exists for this trade', ['trade_id' => $trade->id]);
            return ['success' => true, 'message' => 'Deduction was already applied', 'transaction' => ['id' => 'existing']];
        }
        
        return $this->processDeduction(
            $trade->seller_code,
            'trade',
            $trade->id,
            $tradeAmount
        );
    }

    /**
     * Check if a deduction already exists for a transaction
     * 
     * @param string $type 'order' or 'trade'
     * @param int $id The transaction ID
     * @return bool
     */
    protected function deductionExists($type, $id)
    {
        return WalletTransaction::where(function($query) use ($type) {
                $query->where('reference_type', $type)
                      ->orWhere('reference_type', 'order');
            })
            ->where('reference_id', $id)
            ->where('type', 'debit')
            ->where('description', 'like', '%Platform fee%')
            ->exists();
    }

    /**
     * Core method to process wallet deduction
     *
     * @param string $sellerCode
     * @param string $transactionType
     * @param int $transactionId
     * @param float $transactionAmount
     * @return array
     */
    protected function processDeduction($sellerCode, $transactionType, $transactionId, $transactionAmount)
    {
        // Skip if any essential parameters are missing
        if (empty($sellerCode) || empty($transactionType) || empty($transactionId) || $transactionAmount <= 0) {
            Log::error('Invalid parameters for wallet deduction', [
                'seller_code' => $sellerCode,
                'transaction_type' => $transactionType,
                'transaction_id' => $transactionId,
                'amount' => $transactionAmount
            ]);
            return ['success' => false, 'message' => 'Invalid deduction parameters'];
        }

        try {
            // Start transaction
            DB::beginTransaction();
            
            // 1. Find the seller's wallet
            $wallet = SellerWallet::where('seller_code', $sellerCode)
                ->where('is_activated', true)
                ->where('status', 'active')
                ->first();
            
            if (!$wallet) {
                DB::rollBack();
                Log::error('Active wallet not found for seller', ['seller_code' => $sellerCode]);
                return ['success' => false, 'message' => 'Seller wallet not found or not active'];
            }
            
            // 2. Get the deduction rate from settings
            $rate = (float)Setting::where('key', 'wallet_deduction_rate')->value('value') ?? 5;
            $deductionAmount = round(($transactionAmount * $rate) / 100, 2);
            
            // Ensure minimum deduction of 1 peso if transaction amount is substantial
            if ($deductionAmount < 1 && $transactionAmount >= 20) {
                $deductionAmount = 1.00;
            }
            
            // 3. Calculate new balance
            $previousBalance = $wallet->balance;
            $newBalance = $previousBalance - $deductionAmount;
            
            // Prevent negative balance
            if ($newBalance < 0) {
                $newBalance = 0;
                $deductionAmount = $previousBalance;
            }
            
            // 4. Create wallet transaction record
            $description = "Platform fee: {$rate}% of ₱" . number_format($transactionAmount, 2) . 
                           " (" . ($transactionType === 'trade' ? 'trade' : 'order') . ")";

            $transaction = WalletTransaction::create([
                'user_id' => $wallet->user_id,
                'seller_code' => $sellerCode,
                'type' => 'debit',
                'amount' => $deductionAmount,
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance,
                'reference_type' => 'order', // Use 'order' for all deductions
                'reference_id' => $transactionId,
                'status' => 'completed',
                'description' => $description,
                'processed_at' => now(),
            ]);
            
            // 5. Update wallet balance
            $wallet->balance = $newBalance;
            $wallet->save();
            
            // Commit transaction
            DB::commit();
            
            Log::info('Wallet deduction completed successfully', [
                'seller_code' => $sellerCode,
                'transaction_id' => $transaction->id,
                'deduction_amount' => $deductionAmount,
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance,
            ]);
            
            return [
                'success' => true,
                'message' => 'Fee deduction applied successfully',
                'transaction' => $transaction
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process wallet deduction', [
                'seller_code' => $sellerCode,
                'transaction_type' => $transactionType,
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error processing fee deduction: ' . $e->getMessage()
            ];
        }
    }
}
