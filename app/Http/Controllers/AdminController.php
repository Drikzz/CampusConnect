<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Category; // Import the Category model
use App\Models\Transaction;
use App\Models\Order; // Import the Order model
use App\Models\SellerWallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Import the Log facade
use Inertia\Inertia;

class AdminController extends Controller
{
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function dashboard()
    {
        // Get the base counts
        $pendingRequests = WalletTransaction::where('status', 'pending')->count();
        $totalUsers = User::count();
        $totalProducts = Product::count();
        $totalOrders = Order::count();

        // Get counts of new items today
        $today = now()->startOfDay();
        $newUsers = User::where('created_at', '>=', $today)->count();
        $newProducts = Product::where('created_at', '>=', $today)->count();
        $newOrders = Order::where('created_at', '>=', $today)->count();

        // Get recent wallet transactions with user info
        $recentTransactions = WalletTransaction::with(['user:id,first_name,last_name,seller_code'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'type' => $transaction->amount > 0 ? 'credit' : 'debit',
                    'status' => $transaction->status,
                    'reference_type' => ucfirst($transaction->reference_type),
                    'created_at' => $transaction->created_at,
                    'user' => $transaction->user ? [
                        'name' => $transaction->user->first_name . ' ' . $transaction->user->last_name,
                        'seller_code' => $transaction->user->seller_code
                    ] : null
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'pendingRequests' => $pendingRequests,
                'totalUsers' => $totalUsers,
                'totalProducts' => $totalProducts,
                'totalOrders' => $totalOrders,
                'newUsers' => $newUsers,
                'newProducts' => $newProducts,
                'newOrders' => $newOrders,
                'recentTransactions' => $recentTransactions
            ]
        ]);
    }

    public function walletRequests()
    {
        $transactions = WalletTransaction::with([
            'wallet.user' => function ($query) {
                $query->select('id', 'seller_code', 'first_name', 'last_name');
            }
        ])
            ->latest()
            ->get();

        return Inertia::render('Admin/WalletRequests', [
            'transactions' => $transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'seller_code' => $transaction->seller_code,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'previous_balance' => $transaction->previous_balance,
                    'new_balance' => $transaction->new_balance,
                    'reference_type' => $transaction->reference_type,
                    'reference_id' => $transaction->reference_id,
                    'status' => $transaction->status,
                    'description' => $transaction->description,
                    'receipt_url' => $transaction->receipt_url,
                    'created_at' => $transaction->created_at,
                    'processed_at' => $transaction->processed_at,
                    'wallet' => $transaction->wallet ? [
                        'id' => $transaction->wallet->id,
                        'balance' => $transaction->wallet->balance,
                        'user' => [
                            'first_name' => $transaction->wallet->user->first_name,
                            'last_name' => $transaction->wallet->user->last_name,
                        ]
                    ] : null
                ];
            })
        ]);
    }

    public function approveWalletRequest($id)
    {
        try {
            DB::beginTransaction();

            $transaction = WalletTransaction::findOrFail($id);
            $wallet = $transaction->wallet;

            // Update transaction status
            $transaction->update([
                'status' => 'Completed',
                'processed_at' => now(),
                'processed_by' => auth()->id()
            ]);

            // If this was a verification transaction, activate the wallet
            if ($transaction->reference_type === 'verification') {
                $wallet->update([
                    'is_activated' => true,
                    'status' => 'active',
                    'activated_at' => now()
                ]);
            } else {
                // Handle regular refill transaction
                $wallet->increment('balance', $transaction->amount);
            }

            DB::commit();

            return back()->with('success', 'Wallet request approved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet approval error: ' . $e->getMessage());
            return back()->with('error', 'Failed to approve wallet request');
        }
    }

    public function rejectWalletRequest($id)
    {
        try {
            DB::beginTransaction();

            $transaction = WalletTransaction::findOrFail($id);
            $wallet = $transaction->wallet;

            // Update transaction status with rejection details
            $transaction->update([
                'status' => 'rejected',
                'processed_at' => now(),
                'processed_by' => auth()->id(),
                'remarks' => $transaction->reference_type === 'verification'
                    ? WalletTransaction::$remarks['verification_rejected']
                    : WalletTransaction::$remarks['refill_rejected']
            ]);

            // Set wallet status back to pending but keep track of rejection
            if ($transaction->reference_type === 'verification') {
                $wallet->update([
                    'status' => 'pending',
                    'is_activated' => false
                ]);
            }

            DB::commit();

            Log::info('Wallet request rejected:', [
                'transaction_id' => $transaction->id,
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id
            ]);

            return back()->with('success', 'Wallet verification request rejected');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet rejection error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to reject wallet request');
        }
    }

    public function wallet()
    {
        // Get all sellers with their wallet information
        $sellers = User::where('is_seller', true)
            ->with(['wallet', 'products'])
            ->get()
            ->map(function ($seller) {
                return [
                    'id' => $seller->id,
                    'name' => $seller->first_name . ' ' . $seller->last_name,
                    'seller_code' => $seller->seller_code,
                    'balance' => $seller->wallet?->balance ?? 0,
                    'total_listings' => $seller->products->count(),
                    'fees_collected' => WalletTransaction::where('wallet_id', $seller->wallet?->id)
                        ->where('type', 'fee')
                        ->sum('amount')
                ];
            });

        // Get refund requests
        $refundRequests = RefundRequest::with(['buyer', 'order'])
            ->latest()
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'buyer_name' => $request->buyer->first_name . ' ' . $request->buyer->last_name,
                    'amount' => $request->amount,
                    'reason' => $request->reason,
                    'proof_image' => $request->proof_image ? asset('storage/' . $request->proof_image) : null,
                    'status' => $request->status,
                    'created_at' => $request->created_at
                ];
            });

        // Get wallet stats
        $stats = [
            'revenue' => WalletTransaction::where('type', 'fee')
                ->whereMonth('created_at', now())
                ->sum('amount'),
            'refunds' => WalletTransaction::where('type', 'refund')
                ->whereMonth('created_at', now())
                ->sum('amount'),
            'top_sellers' => User::where('is_seller', true)
                ->whereHas('wallet')
                ->withSum('wallet as balance', 'balance')
                ->orderByDesc('balance')
                ->take(5)
                ->get()
                ->map(fn($seller) => [
                    'name' => $seller->first_name . ' ' . $seller->last_name,
                    'balance' => $seller->balance
                ])
        ];

        return Inertia::render('Admin/Wallet', [
            'sellers' => $sellers,
            'refundRequests' => $refundRequests,
            'stats' => $stats
        ]);
    }
}
