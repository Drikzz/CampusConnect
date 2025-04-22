<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\TradeTransaction;
use App\Models\OrderItem;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Http\Request;

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
            
        // Generate chart data directly from database
        // 1. User Growth Chart Data - last 30 days
        $userChartData = $this->getUserChartData();
        
        // 2. Product Chart Data
        $productChartData = $this->getProductChartData();
        
        // 3. Transaction Chart Data
        $transactionChartData = $this->getTransactionChartData();
            
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
                // Properly structured chart data
                'userStats' => $userChartData,
                'productStats' => $productChartData,
                'transactionStats' => $transactionChartData,
            ],
        ]);
    }

    /**
     * Get user growth chart data
     * 
     * @return array
     */
    private function getUserChartData()
    {
        // Get the last 30 days
        $dates = [];
        $totalUsersData = [];
        $verifiedUsersData = [];
        
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        // Create a dates array for the last 30 days
        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            $dates[] = $date->format('M d');
            
            // Count total users up to this date
            $totalUsers = User::where('created_at', '<=', $date)->count();
            $totalUsersData[] = $totalUsers;
            
            // Count verified users up to this date
            $verifiedUsers = User::whereNotNull('email_verified_at')
                ->where('email_verified_at', '<=', $date)
                ->count();
            $verifiedUsersData[] = $verifiedUsers;
        }
        
        // Return in Chart.js format
        return [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Total Users',
                    'data' => $totalUsersData,
                    'borderColor' => 'rgb(200, 0, 0)',
                    'backgroundColor' => 'rgba(200, 0, 0, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.2
                ],
                [
                    'label' => 'Verified Users',
                    'data' => $verifiedUsersData,
                    'borderColor' => 'rgb(0, 114, 189)',
                    'backgroundColor' => 'rgba(0, 114, 189, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.2
                ]
            ]
        ];
    }
    
    /**
     * API endpoint for filtered user data
     */
    public function getUserChartDataFiltered(Request $request)
    {
        // Extract filter parameters
        $dateRange = $request->input('dateRange', '30days');
        $status = $request->input('status', 'all');
        
        // Calculate date range
        $endDate = Carbon::now()->endOfDay();
        $startDate = $this->getStartDateFromRange($dateRange, $endDate);
        
        $dates = [];
        $datasets = [];
        
        // Create dates array
        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            $dates[] = $date->format('M d');
        }
        
        // Prepare datasets based on status filter
        if ($status === 'all' || $status === 'verified') {
            $verifiedUsersData = [];
            
            for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
                // Count verified users up to this date
                $verifiedUsers = User::whereNotNull('email_verified_at')
                    ->where('email_verified_at', '<=', $date)
                    ->count();
                $verifiedUsersData[] = $verifiedUsers;
            }
            
            $datasets[] = [
                'label' => 'Verified Users',
                'data' => $verifiedUsersData,
                'borderColor' => 'rgb(0, 114, 189)',
                'backgroundColor' => 'rgba(0, 114, 189, 0.1)',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.2
            ];
        }
        
        if ($status === 'all' || $status === 'unverified') {
            $totalUsersData = [];
            $unverifiedUsersData = [];
            
            for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
                // Count total users up to this date
                $totalUsers = User::where('created_at', '<=', $date)->count();
                $totalUsersData[] = $totalUsers;
                
                // Count verified users up to this date (for subtraction)
                $verifiedUsers = User::whereNotNull('email_verified_at')
                    ->where('email_verified_at', '<=', $date)
                    ->count();
                
                // Calculate unverified users
                $unverifiedUsersData[] = $totalUsers - $verifiedUsers;
            }
            
            if ($status === 'all') {
                $datasets[] = [
                    'label' => 'Total Users',
                    'data' => $totalUsersData,
                    'borderColor' => 'rgb(200, 0, 0)',
                    'backgroundColor' => 'rgba(200, 0, 0, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.2
                ];
            } else {
                $datasets[] = [
                    'label' => 'Unverified Users',
                    'data' => $unverifiedUsersData,
                    'borderColor' => 'rgb(255, 127, 14)',
                    'backgroundColor' => 'rgba(255, 127, 14, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.2
                ];
            }
        }
        
        return response()->json([
            'labels' => $dates,
            'datasets' => $datasets
        ]);
    }
    
    /**
     * API endpoint for filtered product data
     */
    public function getProductChartDataFiltered(Request $request)
    {
        // Extract filter parameters
        $dateRange = $request->input('dateRange', '30days');
        $status = $request->input('status', 'all');
        
        // Calculate date range
        $endDate = Carbon::now();
        $startDate = $this->getStartDateFromRange($dateRange, $endDate);
        
        // For simplicity, we'll use weekly intervals regardless of date range
        $dates = [];
        $forSaleData = [];
        $forTradeData = [];
        
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $weekStart = clone $currentDate;
            $weekEnd = (clone $currentDate)->addWeek()->subDay();
            
            if ($weekEnd > $endDate) {
                $weekEnd = clone $endDate;
            }
            
            $dates[] = $weekStart->format('M d');
            
            // Apply status filter to the product query
            $saleQuery = Product::where('is_buyable', true)
                ->where('created_at', '>=', $weekStart)
                ->where('created_at', '<=', $weekEnd);
                
            $tradeQuery = Product::where('is_tradable', true)
                ->where('created_at', '>=', $weekStart)
                ->where('created_at', '<=', $weekEnd);
            
            // Add status filter if specific status requested
            if ($status !== 'all') {
                $saleQuery->where('status', ucfirst($status));
                $tradeQuery->where('status', ucfirst($status));
            }
            
            $forSaleData[] = $saleQuery->count();
            $forTradeData[] = $tradeQuery->count();
            
            $currentDate->addWeek();
        }
        
        return response()->json([
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'For Sale',
                    'data' => $forSaleData,
                    'backgroundColor' => 'rgba(0, 158, 115, 0.1)',
                    'borderColor' => 'rgb(0, 158, 115)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'For Trade',
                    'data' => $forTradeData,
                    'backgroundColor' => 'rgba(213, 94, 0, 0.1)',
                    'borderColor' => 'rgb(213, 94, 0)',
                    'borderWidth' => 1
                ]
            ]
        ]);
    }
    
    /**
     * API endpoint for filtered transaction data
     */
    public function getTransactionChartDataFiltered(Request $request)
    {
        // Extract filter parameters
        $dateRange = $request->input('dateRange', '30days');
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
     * Helper to get start date from a date range string
     */
    private function getStartDateFromRange($dateRange, $endDate) 
    {
        $startDate = clone $endDate;
        
        switch ($dateRange) {
            case '7days':
                $startDate->subDays(7);
                break;
            case '30days':
                $startDate->subDays(30);
                break;
            case '90days':
                $startDate->subDays(90);
                break;
            case 'year':
                $startDate->subYear();
                break;
            case 'all':
                // Go back 5 years for "all" - adjust as needed
                $startDate->subYears(5);
                break;
            default:
                $startDate->subDays(30); // Default to 30 days
        }
        
        return $startDate->startOfDay();
    }

    /**
     * Get product listings chart data
     * 
     * @return array
     */
    private function getProductChartData()
    {
        // Get product data for the last 12 weeks
        $dates = [];
        $forSaleData = [];
        $forTradeData = [];
        
        // Get week intervals for the last 12 weeks
        $now = Carbon::now();
        for ($i = 11; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();
            
            $dates[] = $weekStart->format('M d');
            
            // Count products by type for this week
            $forSale = Product::where('is_buyable', true)
                ->where('created_at', '>=', $weekStart)
                ->where('created_at', '<=', $weekEnd)
                ->count();
                
            $forTrade = Product::where('is_tradable', true)
                ->where('created_at', '>=', $weekStart)
                ->where('created_at', '<=', $weekEnd)
                ->count();
                
            $forSaleData[] = $forSale;
            $forTradeData[] = $forTrade;
        }
        
        // Return in Chart.js format
        return [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'For Sale',
                    'data' => $forSaleData,
                    'backgroundColor' => 'rgba(0, 158, 115, 0.1)',
                    'borderColor' => 'rgb(0, 158, 115)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'For Trade',
                    'data' => $forTradeData,
                    'backgroundColor' => 'rgba(213, 94, 0, 0.1)',
                    'borderColor' => 'rgb(213, 94, 0)',
                    'borderWidth' => 1
                ]
            ]
        ];
    }
    
    /**
     * Get transaction analysis chart data
     * 
     * @return array
     */
    private function getTransactionChartData()
    {
        // Get transaction data for the last 30 days
        $dates = [];
        $ordersData = [];
        $tradesData = [];
        $walletData = [];
        
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        // Create a dates array for the last 30 days
        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            $currentDate = clone $date;
            $dates[] = $currentDate->format('M d');
            
            // Count orders for this day
            $orders = Order::whereDate('created_at', $currentDate)->count();
            $ordersData[] = $orders;
            
            // Count trades for this day
            $trades = TradeTransaction::whereDate('created_at', $currentDate)->count();
            $tradesData[] = $trades;
            
            // Count wallet transactions for this day
            $wallet = WalletTransaction::whereDate('created_at', $currentDate)->count();
            $walletData[] = $wallet;
        }
        
        // Return in Chart.js format
        return [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $ordersData,
                    'borderColor' => 'rgb(86, 180, 233)',
                    'backgroundColor' => 'rgba(86, 180, 233, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.2
                ],
                [
                    'label' => 'Trades',
                    'data' => $tradesData,
                    'borderColor' => 'rgb(204, 121, 167)',
                    'backgroundColor' => 'rgba(204, 121, 167, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.2
                ],
                [
                    'label' => 'Wallet Transactions',
                    'data' => $walletData,
                    'borderColor' => 'rgb(230, 159, 0)',
                    'backgroundColor' => 'rgba(230, 159, 0, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.2
                ]
            ]
        ];
    }
}
