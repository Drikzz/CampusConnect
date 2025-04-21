<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\TradeTransaction;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminTransactionsController extends Controller
{
    /**
     * Display the transactions management interface.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        // Extract filter parameters
        $type = $request->input('type', 'all');
        $status = $request->input('status', 'all');
        $dateRange = $request->input('date_range', '30days');
        
        // Calculate date range for filtering
        $endDate = Carbon::now()->endOfDay();
        $startDate = $this->getStartDateFromRange($dateRange, $endDate);
        
        // Get transaction counts for stats cards
        $stats = $this->getTransactionStats($startDate);
        
        // Get transactions based on filters
        $transactions = $this->getFilteredTransactions($type, $status, $startDate, $endDate);
        
        return Inertia::render('Admin/admin-transactions', [
            'stats' => $stats,
            'transactions' => $transactions,
            'filters' => [
                'type' => $type,
                'status' => $status,
                'date_range' => $dateRange,
            ]
        ]);
    }
    
    /**
     * Get filtered transaction data for charts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChartData(Request $request)
    {
        // Extract filter parameters
        $dateRange = $request->input('date_range', '30days');
        $category = $request->input('category', 'all');
        
        // Calculate date range
        $endDate = Carbon::now()->endOfDay();
        $startDate = $this->getStartDateFromRange($dateRange, $endDate);
        
        $dates = [];
        $ordersData = [];
        $tradesData = [];
        $walletData = [];
        
        // Generate dates in range
        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            $currentDate = clone $date;
            $dates[] = $currentDate->format('M d');
            
            // Only calculate data for requested categories
            if ($category === 'all' || $category === 'orders') {
                $orders = Order::whereDate('created_at', $currentDate)->count();
                $ordersData[] = $orders;
            }
            
            if ($category === 'all' || $category === 'trades') {
                $trades = TradeTransaction::whereDate('created_at', $currentDate)->count();
                $tradesData[] = $trades;
            }
            
            if ($category === 'all' || $category === 'wallet') {
                $wallet = WalletTransaction::whereDate('created_at', $currentDate)->count();
                $walletData[] = $wallet;
            }
        }
        
        // Build datasets based on requested categories
        $datasets = [];
        
        if ($category === 'all' || $category === 'orders') {
            $datasets[] = [
                'label' => 'Orders',
                'data' => $ordersData,
                'borderColor' => 'rgb(86, 180, 233)',
                'backgroundColor' => 'rgba(86, 180, 233, 0.1)',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.2
            ];
        }
        
        if ($category === 'all' || $category === 'trades') {
            $datasets[] = [
                'label' => 'Trades',
                'data' => $tradesData,
                'borderColor' => 'rgb(204, 121, 167)',
                'backgroundColor' => 'rgba(204, 121, 167, 0.1)',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.2
            ];
        }
        
        if ($category === 'all' || $category === 'wallet') {
            $datasets[] = [
                'label' => 'Wallet Transactions',
                'data' => $walletData,
                'borderColor' => 'rgb(230, 159, 0)',
                'backgroundColor' => 'rgba(230, 159, 0, 0.1)',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.2
            ];
        }
        
        return response()->json([
            'labels' => $dates,
            'datasets' => $datasets
        ]);
    }
    
    /**
     * Get transaction statistics.
     *
     * @param  \Carbon\Carbon  $startDate
     * @return array
     */
    private function getTransactionStats($startDate)
    {
        return [
            'wallet_requests_count' => WalletTransaction::count(),
            'pending_wallet_requests' => WalletTransaction::whereIn('status', ['pending', 'in_process'])->count(),
            
            'trades_count' => TradeTransaction::count(),
            'completed_trades_count' => TradeTransaction::where('status', 'completed')->count(),
            
            'orders_count' => Order::count(),
            'completed_orders_count' => Order::where('status', 'Completed')->count(),
        ];
    }
    
    /**
     * Get filtered transactions based on provided parameters.
     *
     * @param  string  $type
     * @param  string  $status
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    private function getFilteredTransactions($type, $status, $startDate, $endDate)
    {
        $transactions = [
            'orders' => [],
            'trades' => [],
            'wallet' => []
        ];
        
        // Filter by date range applies to all transaction types
        
        // Get Orders with buyer information
        if ($type === 'all' || $type === 'orders') {
            $orderQuery = Order::with(['buyer:id,first_name,last_name,seller_code', 'items.product'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc');
                
            // Apply status filter if provided
            if ($status !== 'all') {
                // In Orders, status field first letter is uppercase (e.g., 'Pending', 'Completed')
                $orderQuery->where('status', ucfirst($status));
            }
            
            $transactions['orders'] = $orderQuery->get()
                ->map(function ($order) {
                    // Calculate total from items if total is not available
                    $total = $order->items->sum(function($item) {
                        return $item->price * $item->quantity;
                    });
                    
                    return [
                        'id' => $order->id,
                        'user' => [
                            'name' => $order->buyer ? 
                                $order->buyer->first_name . ' ' . $order->buyer->last_name : 'N/A',
                            'seller_code' => $order->buyer->seller_code ?? null,
                        ],
                        'amount' => $total,
                        'status' => $order->status,
                        'created_at' => $order->created_at,
                    ];
                });
        }
        
        // Get Trades with buyer and seller product info
        if ($type === 'all' || $type === 'trades') {
            $tradeQuery = TradeTransaction::with([
                    'buyer:id,first_name,last_name,seller_code',
                    'sellerProduct:id,name,price,images', 
                    'offeredItems'
                ])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc');
                
            // Apply status filter if provided
            if ($status !== 'all') {
                // In TradeTransaction, status is lowercase (e.g., 'pending', 'completed')
                $tradeQuery->where('status', strtolower($status));
            }
            
            $transactions['trades'] = $tradeQuery->get()
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
                        'product_name' => $trade->sellerProduct ? $trade->sellerProduct->name : 'Unknown Product',
                        'product_value' => $productValue,
                        'offered_items_value' => $offeredItemsValue,
                        'additional_cash' => $additionalCash,
                        'amount' => $totalValue,
                        'status' => $trade->status,
                        'created_at' => $trade->created_at,
                    ];
                });
        }
        
        // Get Wallet Transactions
        if ($type === 'all' || $type === 'wallet') {
            $walletQuery = WalletTransaction::with(['user:id,first_name,last_name,seller_code'])
                ->whereIn('reference_type', ['refill', 'withdrawal', 'verification'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc');
                
            // Apply status filter if provided
            if ($status !== 'all') {
                $walletQuery->where('status', $status);
            }
            
            $transactions['wallet'] = $walletQuery->get()
                ->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'user' => [
                            'name' => $transaction->user ? 
                                $transaction->user->first_name . ' ' . $transaction->user->last_name : 'N/A',
                            'seller_code' => $transaction->user->seller_code ?? null,
                        ],
                        'amount' => (float)$transaction->amount,
                        'type' => $transaction->type,
                        'reference_type' => $transaction->reference_type,
                        'status' => $transaction->status,
                        'created_at' => $transaction->created_at,
                    ];
                });
        }
        
        return $transactions;
    }
    
    /**
     * Get start date from date range.
     *
     * @param  string  $dateRange
     * @param  \Carbon\Carbon  $endDate
     * @return \Carbon\Carbon
     */
    private function getStartDateFromRange($dateRange, $endDate)
    {
        switch ($dateRange) {
            case '7days':
                return Carbon::now()->subDays(7)->startOfDay();
            case '30days':
                return Carbon::now()->subDays(30)->startOfDay();
            case '90days':
                return Carbon::now()->subDays(90)->startOfDay();
            case 'year':
                return Carbon::now()->subYear()->startOfDay();
            case 'all':
            default:
                return Carbon::now()->subYears(5)->startOfDay(); // 5 years is effectively "all"
        }
    }
}
