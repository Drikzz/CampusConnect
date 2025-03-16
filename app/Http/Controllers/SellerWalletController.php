<?php

namespace App\Http\Controllers;

use App\Models\SellerWallet;
use App\Models\WalletTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class SellerWalletController extends Controller
{
  public function index()
  {
    $user = auth()->user();

    // Get wallet with transactions
    $wallet = SellerWallet::where('user_id', $user->id)
      ->with(['transactions' => function ($query) {
        $query->latest()->take(5);
      }])
      ->first();

    // Create wallet if it doesn't exist
    if (!$wallet) {
      $wallet = SellerWallet::create([
        'user_id' => $user->id,
        'balance' => 0,
        'is_activated' => false,
        'status' => 'pending'
      ]);
    }

    // Get latest verification transaction
    $latestVerification = WalletTransaction::where('user_id', $user->id)
      ->where('reference_type', 'verification')
      ->latest()
      ->first();

    // Log the user data to debug
    Log::info('User data passed to wallet view:', [
      'user_id' => $user->id,
      'user_email' => $user->wmsu_email,
      'user_phone' => $user->phone,
      'has_wallet' => (bool)$wallet
    ]);

    return Inertia::render('Dashboard/seller/Wallet', [
      'user' => $user,
      'wallet' => $wallet,
      'verification' => $latestVerification ? [
        'status' => $latestVerification->status,
        'remarks' => $latestVerification->remarks,
        'description' => $latestVerification->description,
        'processed_at' => $latestVerification->processed_at,
        'submitted_at' => $latestVerification->created_at
      ] : null,
      'stats' => array_merge(
        $this->getDashboardStats($user),
        $this->getWalletStats($wallet)
      )
    ]);
  }

  public function setup(Request $request)
  {
    try {
      Log::info('Wallet setup request:', [
        'user_id' => auth()->id(),
        'seller_code' => auth()->user()->seller_code,
        'request_data' => $request->all(),
        'has_file' => $request->hasFile('id_image')
      ]);

      $request->validate([
        'id_image' => 'required|image|max:2048',
        'terms_accepted' => 'required|accepted'
      ]);

      DB::beginTransaction();

      $user = auth()->user();

      // Find existing wallet and rejected verification
      $wallet = SellerWallet::where('user_id', $user->id)->first();
      $rejectedVerification = WalletTransaction::where('user_id', $user->id)
        ->where('reference_type', 'verification')
        ->where('verification_type', 'seller_activation')
        ->where('status', 'rejected')
        ->first(); // Changed from exists() to first() to get the record

      // Store new ID image
      $idPath = $request->file('id_image')->store('seller-ids', 'public');

      // Update or create wallet 
      $wallet = SellerWallet::updateOrCreate(
        ['user_id' => $user->id],
        [
          'seller_code' => $user->seller_code,
          'status' => SellerWallet::STATUS_PENDING_APPROVAL,
          'is_activated' => false
        ]
      );

      // Update existing verification or create new one
      if ($rejectedVerification) {
        // Update existing verification transaction
        $transaction = $rejectedVerification;
        $transaction->update([
          'verification_data' => [
            'wmsu_email' => $user->wmsu_email,
            'selfie_with_id' => $idPath,
            'agreed_to_terms' => true,
            'is_resubmission' => true
          ],
          'status' => 'pending',
          'processed_at' => null,
          'processed_by' => null,
          'remarks' => null
        ]);
      } else {
        // Create new verification transaction for first-time setup
        $transaction = WalletTransaction::create([
          'user_id' => $user->id,
          'seller_code' => $user->seller_code,
          'type' => 'credit',
          'amount' => 0,
          'reference_type' => 'verification',
          'reference_id' => (string) Str::ulid(),
          'verification_type' => 'seller_activation',
          'verification_data' => [
            'wmsu_email' => $user->wmsu_email,
            'selfie_with_id' => $idPath,
            'agreed_to_terms' => true,
            'is_resubmission' => false
          ],
          'status' => 'pending',
          'description' => 'Wallet Verification Request'
        ]);
      }

      DB::commit();

      $message = $rejectedVerification ?
        'Verification request resubmitted successfully' :
        'Verification request submitted successfully';

      return back()->with('success', $message);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Wallet setup error:', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'user_id' => auth()->id(),
        'seller_code' => auth()->user()->seller_code
      ]);

      return back()->withErrors(['error' => 'Failed to submit verification request: ' . $e->getMessage()]);
    }
  }

  public function refill(Request $request)
  {
    $wallet = auth()->user()->wallet;

    if (!$wallet || !$wallet->is_activated) {
      return back()->with('error', 'Please complete wallet setup first');
    }

    $request->validate([
      'amount' => 'required|numeric|min:100',
      'reference_number' => 'required|string',
      'receipt_image' => 'required|image|max:2048'
    ]);

    try {
      DB::beginTransaction();

      // Store receipt image
      $path = $request->file('receipt_image')->store('receipts', 'public');

      // Create pending transaction
      $transaction = WalletTransaction::create([
        'user_id' => auth()->id(),
        'seller_code' => auth()->user()->seller_code,
        'type' => 'credit',
        'amount' => $request->amount,
        'previous_balance' => $wallet->balance,
        'new_balance' => $wallet->balance + $request->amount,
        'reference_type' => 'refill',
        'reference_id' => $request->reference_number,
        'status' => 'pending',
        'description' => 'Wallet refill request',
        'receipt_path' => $path
      ]);

      DB::commit();

      return back()->with('success', 'Refill request submitted for admin approval');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Wallet refill error: ' . $e->getMessage());
      return back()->with('error', 'Failed to submit refill request');
    }
  }

  public function withdraw(Request $request)
  {
    // Simple validation
    $request->validate([
      'amount' => 'required|numeric|min:100',
      'phone_number' => 'required|string',
      'account_name' => 'nullable|string|max:255'
    ]);

    $user = auth()->user();
    $wallet = $user->wallet;

    // Check wallet status and balance
    if (!$wallet || $wallet->status !== 'active') {
      return back()->with('error', 'Wallet not active');
    }

    if ($wallet->balance < $request->amount) {
      return back()->with('error', 'Insufficient balance');
    }

    DB::beginTransaction();

    // Create withdrawal transaction
    $transaction = WalletTransaction::create([
      'user_id' => $user->id,
      'seller_code' => $user->seller_code,
      'type' => 'debit',
      'amount' => $request->amount,
      'previous_balance' => $wallet->balance,
      'new_balance' => $wallet->balance - $request->amount,
      'reference_type' => 'withdrawal',
      'reference_id' => (string) Str::ulid(),
      'phone_number' => $request->phone_number,
      'account_name' => $request->account_name,
      'status' => 'pending',
      'description' => 'GCash withdrawal request',
      'remarks' => "GCash: {$request->phone_number}" .
        ($request->account_name ? " | Account Name: {$request->account_name}" : '')
    ]);

    // Check if the transaction was created successfully
    if ($transaction) {
      DB::commit();
      return back()->with('success', 'Withdrawal request submitted for approval');
    } else {
      DB::rollBack();
      Log::error('Wallet withdrawal error: Failed to create transaction');
      return back()->with('error', 'Failed to submit withdrawal request');
    }
  }

  // Update the getWalletStatus method
  public function getWalletStatus()
  {
    try {
      $user = auth()->user();
      $wallet = SellerWallet::where('user_id', $user->id)
        ->with(['transactions' => function ($query) {
          $query->latest()->take(5);
        }])
        ->first();

      // Get latest verification transaction
      $latestVerification = WalletTransaction::where('user_id', $user->id)
        ->where('reference_type', 'verification')
        ->latest()
        ->first();

      Log::info('Fetched wallet status:', [
        'wallet_status' => $wallet?->status,
        'verification_status' => $latestVerification?->status,
        'verification_remarks' => $latestVerification?->remarks
      ]);

      return response()->json([
        'id' => $wallet->id,
        'balance' => $wallet->balance,
        'status' => strtolower($wallet->status),
        'is_activated' => $wallet->is_activated,
        'created_at' => $wallet->created_at,
        'transactions' => $wallet->transactions,
        'verification' => $latestVerification ? [
          'status' => $latestVerification->status,
          'remarks' => $latestVerification->remarks,
          'description' => $latestVerification->description,
          'processed_at' => $latestVerification->processed_at,
          'submitted_at' => $latestVerification->created_at
        ] : null
      ]);
    } catch (\Exception $e) {
      Log::error('Wallet status fetch error:', [
        'user_id' => auth()->id(),
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      return response()->json([
        'error' => 'Server error',
        'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
      ], 500);
    }
  }

  /**
   * Generate and download a PDF receipt for a transaction
   */
  public function downloadReceipt($id)
  {
    try {
      $transaction = WalletTransaction::where('id', $id)
        ->where('user_id', auth()->id()) // Ensure the user owns the transaction
        ->firstOrFail();

      // Generate PDF
      $pdf = Pdf::loadView('pdf.receipt', compact('transaction'));

      // Set filename based on transaction type
      $filename = "CampusConnect_" . ucfirst($transaction->reference_type) . "_Receipt_" . $id . ".pdf";

      return $pdf->download($filename);
    } catch (\Exception $e) {
      Log::error('Receipt download error: ' . $e->getMessage(), [
        'transaction_id' => $id,
        'user_id' => auth()->id()
      ]);

      return back()->with('error', 'Failed to generate receipt. Please try again.');
    }
  }

  private function getWalletStats($wallet)
  {
    if (!$wallet) {
      return [
        'total_transactions' => 0,
        'total_credits' => 0,
        'total_debits' => 0,
        'pending_transactions' => 0
      ];
    }

    $transactions = $wallet->transactions();

    return [
      'total_transactions' => $transactions->count(),
      'total_credits' => $transactions->where('reference_type', 'refill')
        ->where('status', 'Completed')
        ->sum('amount'),
      'total_debits' => $transactions->whereIn('reference_type', ['withdraw', 'deduction'])
        ->where('status', 'Completed')
        ->sum('amount'),
      'pending_transactions' => $transactions->where('status', 'pending')->count()
    ];
  }

  private function getDashboardStats($user)
  {
    $stats = [
      'totalOrders' => Order::where('buyer_id', $user->id)->count(),
      'activeOrders' => Order::where('buyer_id', $user->id)
        ->whereNotIn('status', ['Completed', 'Cancelled'])
        ->count(),
      'wishlistCount' => Wishlist::where('user_id', $user->id)->count(),
      'totalSales' => 0,
      'activeProducts' => 0,
      'pendingOrders' => 0
    ];

    if ($user->is_seller) {
      $stats['totalSales'] = OrderItem::where('seller_code', $user->seller_code)
        ->whereHas('order', function ($query) {
          $query->where('status', 'Completed');
        })
        ->sum('subtotal');

      $stats['activeProducts'] = Product::where('seller_code', $user->seller_code)
        ->where('status', 'Active')
        ->count();

      $stats['pendingOrders'] = OrderItem::where('seller_code', $user->seller_code)
        ->whereHas('order', function ($query) {
          $query->where('status', 'Pending');
        })->count();

      // Add wallet details - This is the important part
      $wallet = SellerWallet::where('seller_code', $user->seller_code)
        ->with(['transactions' => function ($query) {
          $query->latest()->take(5);  // Fixed: Add missing parenthesis
        }])
        ->first();

      $stats['wallet'] = $wallet ? [
        'id' => $wallet->id,
        'balance' => $wallet->balance,
        'is_activated' => $wallet->is_activated,
        'status' => $wallet->status,
        'transactions' => $wallet->transactions
      ] : null;
    }

    return $stats;
  }
}
