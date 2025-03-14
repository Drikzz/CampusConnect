<?php

namespace App\Http\Controllers;

use App\Http\Middleware\Verification;
use App\Mail\SellerRegistrationConfirmation;
use App\Mail\WalletTransactionApproved;
use App\Mail\WalletTransactionRejected;
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
use Illuminate\Support\Facades\Mail;
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
                $query->select('id', 'seller_code', 'first_name', 'last_name', 'wmsu_email');
            }
        ])
            ->latest()
            ->get();

        // dd($transactions->user_id->first_name);
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
                            'wmsu_email' => $transaction->wallet->user->wmsu_email,
                        ]
                    ] : null
                ];
            })
        ]);
    }

    public function approveWalletRequest($id)
    {
        DB::beginTransaction();

        try {
            // 1. Find and log the transaction data for debugging
            $transaction = WalletTransaction::with(['wallet.user'])->findOrFail($id);
            Log::info('Transaction found', ['id' => $transaction->id, 'reference_type' => $transaction->reference_type]);

            // 2. Verify wallet relationship
            $wallet = $transaction->wallet;
            if (!$wallet) {
                DB::rollBack();
                Log::error('Wallet not found for transaction', ['transaction_id' => $id]);
                return back()->with('error', 'Failed to find wallet for this transaction');
            }
            Log::info('Wallet found', ['wallet_id' => $wallet->id, 'balance' => $wallet->balance]);

            // 3. Verify user relationship and email
            $user = $wallet->user;
            if (!$user || !$user->wmsu_email) {
                DB::rollBack();
                Log::error('User or email not found', [
                    'transaction_id' => $id,
                    'user_exists' => (bool)$user,
                    'email_exists' => $user ? (bool)$user->wmsu_email : false
                ]);
                return back()->with('error', 'User email not available');
            }
            Log::info('User found', ['user_id' => $user->id, 'email' => $user->wmsu_email]);

            // 4. Update transaction status
            $transaction->update([
                'status' => 'completed',
                'processed_at' => now(),
                'processed_by' => auth()->id()
            ]);
            Log::info('Transaction status updated to completed');

            // 5. Update wallet based on transaction type
            if ($transaction->reference_type === 'verification') {
                $wallet->update([
                    'is_activated' => true,
                    'status' => 'active',
                    'activated_at' => now()
                ]);
                Log::info('Wallet activated successfully');
            } elseif ($transaction->reference_type === 'refill') {
                // Handle regular refill transaction
                $wallet->increment('balance', $transaction->amount);
                Log::info('Wallet balance updated', ['new_balance' => $wallet->balance]);
            } elseif ($transaction->reference_type === 'withdrawal') {
                // Handle withdrawal transaction - Deduct balance when approved
                $wallet->decrement('balance', $transaction->amount);
                Log::info('Wallet withdrawal processed', [
                    'deducted_amount' => $transaction->amount,
                    'new_balance' => $wallet->balance,
                    'phone_number' => $transaction->phone_number,
                    'account_name' => $transaction->account_name
                ]);
            }

            // 6. Attempt to send email with detailed error handling
            try {
                $email = $user->wmsu_email;
                Log::info('Attempting to send email to', ['email' => $email]);
                Mail::to($email)->send(new WalletTransactionApproved($transaction));
                Log::info('Email sent successfully');
            } catch (\Exception $emailError) {
                Log::error('Email sending failed', [
                    'error' => $emailError->getMessage(),
                    'trace' => $emailError->getTraceAsString()
                ]);
                // Continue execution despite email error
            }

            DB::commit();
            Log::info('Transaction approval completed successfully');

            return back()->with('success', 'Wallet request approved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet approval error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to approve wallet request: ' . $e->getMessage());
        }
    }

    public function rejectWalletRequest($id)
    {
        // Start database transaction
        DB::beginTransaction();

        // 1. Find and log the transaction data for debugging
        $transaction = WalletTransaction::with(['wallet.user'])->findOrFail($id);
        Log::info('Rejecting transaction', ['id' => $transaction->id, 'reference_type' => $transaction->reference_type]);

        // 2. Verify wallet relationship
        $wallet = $transaction->wallet;
        if (!$wallet) {
            DB::rollBack();
            Log::error('Wallet not found for transaction', ['transaction_id' => $id]);
            return back()->with('error', 'Failed to find wallet for this transaction');
        }
        Log::info('Wallet found', ['wallet_id' => $wallet->id]);

        // 3. Verify user relationship and email
        $user = $wallet->user;
        if (!$user || !$user->wmsu_email) {
            DB::rollBack();
            Log::error('User or email not found', [
                'transaction_id' => $id,
                'user_exists' => (bool)$user,
                'email_exists' => $user ? (bool)$user->wmsu_email : false
            ]);
            return back()->with('error', 'User email not available');
        }
        Log::info('User found', ['user_id' => $user->id, 'email' => $user->wmsu_email]);

        // 4. Update transaction status with rejection details
        $transaction->update([
            'status' => 'rejected',
            'processed_at' => now(),
            'processed_by' => auth()->id(),
            'remarks' => 'Your request was rejected by the admin.'
        ]);
        Log::info('Transaction status updated to rejected');

        // 5. Handle wallet deactivation if this is a verification transaction
        if ($transaction->reference_type === 'verification' && $transaction->verification_type === 'seller_activation') {
            $wallet->update([
                'is_activated' => false,
                'status' => SellerWallet::STATUS_SUSPENDED, // Use the constant from the model
                'activated_at' => null
            ]);
            Log::info('Wallet deactivated successfully');
        }

        // 6. Add appropriate rejection remarks based on transaction type
        $rejectReason = 'Request rejected by admin.';
        if ($transaction->reference_type === 'verification') {
            $rejectReason = WalletTransaction::$remarks['verification_rejected'] ?? 'Verification rejected by admin.';
        } elseif ($transaction->reference_type === 'refill') {
            $rejectReason = WalletTransaction::$remarks['refill_rejected'] ?? 'Refill request rejected by admin.';
        } elseif ($transaction->reference_type === 'withdrawal') {
            $rejectReason = WalletTransaction::$remarks['withdrawal_rejected'] ?? 'Withdrawal request rejected by admin.';
        }

        // Update with specific reason
        $transaction->update(['remarks' => $rejectReason]);
        Log::info('Added rejection reason', ['reason' => $rejectReason]);

        // 7. Send email notification - SKIP ON FAILURE
        $email = $user->wmsu_email;
        Log::info('Attempting to send rejection email to', ['email' => $email]);

        try {
            Mail::to($email)->send(new WalletTransactionRejected($transaction));
            Log::info('Rejection email sent successfully');
        } catch (\Exception $emailError) {
            Log::error('Rejection email sending failed', [
                'error' => $emailError->getMessage(),
                'trace' => $emailError->getTraceAsString()
            ]);
            // Continue execution despite email error
        }

        // Commit database changes
        DB::commit();
        Log::info('Transaction rejection completed successfully');

        return back()->with('success', 'Wallet request rejected successfully');
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
