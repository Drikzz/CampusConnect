<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\TradeTransaction;
use App\Models\OrderItem;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Get counts for dashboard stats
        $totalUsers = User::count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $pendingListings = Product::where('status', 'Pending')->count();
        
        // Count products by listing type
        $buyableProducts = Product::where('is_buyable', true)
            ->where('status', 'Active')
            ->count();
        $tradableProducts = Product::where('is_tradable', true)
            ->where('status', 'Active')
            ->count();
        
        // Count total transactions (purchases + trades)
        $totalOrders = Order::count();
        $totalTrades = TradeTransaction::count();
        $totalTransactions = $totalOrders + $totalTrades;
        
        // Count successful transactions
        $successfulOrders = Order::where('status', 'Completed')->count();
        $successfulTrades = TradeTransaction::where('status', 'completed')->count();
        $successfulTransactions = $successfulOrders + $successfulTrades;
        
        // Count pending requests
        $refundRequests = Order::where('status', 'Refund Requested')->count();
        $pendingRefills = WalletTransaction::where('reference_type', 'refill')
            ->where('status', 'pending')
            ->count();
        
        // Get recent transactions by type
        
        // 1. Get Orders with buyer information and calculate proper totals
        $recentOrders = Order::with(['buyer:id,first_name,last_name,seller_code', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($order) {
                // Calculate total from items if total is not available
                $calculatedTotal = 0;
                if ($order->items && $order->items->count() > 0) {
                    $calculatedTotal = $order->items->sum(function($item) {
                        return $item->price * $item->quantity;
                    });
                }
                
                // Use total if available, then sub_total as fallback, then calculated total
                $orderTotal = $order->total ?? $order->sub_total ?? $calculatedTotal;
                
                return [
                    'id' => $order->id,
                    'user' => [
                        'name' => $order->buyer ? 
                            $order->buyer->first_name . ' ' . $order->buyer->last_name : 'N/A',
                        'seller_code' => $order->buyer->seller_code ?? null,
                    ],
                    'amount' => $orderTotal,
                    'type' => 'credit',
                    'reference_type' => 'order',
                    'status' => strtolower($order->status),
                    'created_at' => $order->created_at,
                ];
            });
            
        // 2. Get Trades with buyer and seller product info with detailed value breakdown
        $recentTrades = TradeTransaction::with([
                'buyer:id,first_name,last_name,seller_code',
                'sellerProduct:id,name,price,images', 
                'offeredItems'
            ])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($trade) {
                // Calculate each component of the trade value separately
                $productValue = $trade->sellerProduct ? (float)$trade->sellerProduct->price : 0;
                
                // Calculate total value of offered items
                $offeredItemsValue = 0;
                if ($trade->offeredItems && $trade->offeredItems->count() > 0) {
                    $offeredItemsValue = $trade->offeredItems->sum(function($item) {
                        return (float)$item->estimated_value * (int)$item->quantity;
                    });
                }
                
                $additionalCash = (float)($trade->additional_cash ?? 0);
                $totalValue = $productValue + $offeredItemsValue + $additionalCash;
                
                return [
                    'id' => $trade->id,
                    'user' => [
                        'name' => $trade->buyer ? 
                            $trade->buyer->first_name . ' ' . $trade->buyer->last_name : 'N/A',
                        'seller_code' => $trade->buyer->seller_code ?? null,
                    ],
                    'product_value' => $productValue, // Value of the seller's product
                    'offered_items_value' => $offeredItemsValue, // Value of the offered items
                    'additional_cash' => $additionalCash, // Additional cash component
                    'amount' => $totalValue, // Total value of the trade
                    'product_name' => $trade->sellerProduct ? $trade->sellerProduct->name : 'Unknown Product',
                    'type' => 'credit',
                    'reference_type' => 'trade',
                    'status' => $trade->status,
                    'created_at' => $trade->created_at,
                ];
            });
            
        // 3. Get Wallet Transactions
        $recentWalletTransactions = WalletTransaction::with(['user:id,first_name,last_name,seller_code'])
            ->whereIn('reference_type', ['refill', 'withdrawal'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'user' => [
                        'name' => $transaction->user ? 
                            $transaction->user->first_name . ' ' . $transaction->user->last_name : 'N/A',
                        'seller_code' => $transaction->user->seller_code ?? null,
                    ],
                    'amount' => (float)$transaction->amount, // Ensure amount is a number
                    'type' => $transaction->type,
                    'reference_type' => $transaction->reference_type,
                    'status' => $transaction->status,
                    'created_at' => $transaction->created_at,
                ];
            });
            
        // Return all transaction types separately to the view
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'dashboardStats' => [
                    ['name' => 'Total Users', 'value' => (string)$totalUsers, 'icon' => 'UsersIcon'],
                    ['name' => 'Verified Users', 'value' => (string)$verifiedUsers, 'icon' => 'CheckCircleIcon'],
                    ['name' => 'For Sale Products', 'value' => (string)$buyableProducts, 'icon' => 'ShoppingBagIcon'],
                    ['name' => 'For Trade Products', 'value' => (string)$tradableProducts, 'icon' => 'ArrowsRightLeftIcon'],
                    ['name' => 'Pending Listings', 'value' => (string)$pendingListings, 'icon' => 'ClockIcon'],
                    ['name' => 'Total Transactions', 'value' => (string)$totalTransactions, 'icon' => 'CurrencyDollarIcon'],
                    ['name' => 'Successful Transactions', 'value' => (string)$successfulTransactions, 'icon' => 'CheckCircleIcon'],
                    ['name' => 'Refund Requests', 'value' => (string)$refundRequests, 'icon' => 'ExclamationCircleIcon'],
                    ['name' => 'Wallet Refill Requests', 'value' => (string)$pendingRefills, 'icon' => 'BanknotesIcon'],
                ],
                'transactions' => [
                    'orders' => $recentOrders,
                    'trades' => $recentTrades, 
                    'wallet' => $recentWalletTransactions
                ],
            ],
        ]);
    }
}
