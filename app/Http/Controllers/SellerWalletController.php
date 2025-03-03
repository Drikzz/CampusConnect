<?php

namespace App\Http\Controllers;

use App\Models\SellerWallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SellerWalletController extends Controller
{
  public function index()
  {
    $user = auth()->user();
    $wallet = SellerWallet::where('seller_code', $user->seller_code)
      ->with(['transactions' => function ($query) {
        $query->latest()->take(5);
      }])
      ->first();

    return Inertia::render('Dashboard/seller/Wallet', [
      'user' => $user,
      'wallet' => $wallet,
      'stats' => $this->getWalletStats($wallet)
    ]);
  }

  public function activate(Request $request)
  {
    $request->validate([
      'terms_accepted' => 'required|accepted'
    ]);

    try {
      DB::beginTransaction();

      $wallet = SellerWallet::firstOrCreate(
        ['seller_code' => auth()->user()->seller_code],
        [
          'balance' => 0.00,
          'is_activated' => true,
          'status' => 'active',
          'activated_at' => now()
        ]
      );

      DB::commit();

      return redirect()->route('seller.wallet.index')
        ->with('success', 'Wallet activated successfully');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Wallet activation error: ' . $e->getMessage());

      return back()->with('error', 'Failed to activate wallet. Please try again.');
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

    return [
      'total_transactions' => $wallet->transactions()->count(),
      'total_credits' => $wallet->transactions()->where('type', 'credit')
        ->where('status', 'completed')
        ->sum('amount'),
      'total_debits' => $wallet->transactions()->where('type', 'debit')
        ->where('status', 'completed')
        ->sum('amount'),
      'pending_transactions' => $wallet->transactions()
        ->where('status', 'pending')
        ->count()
    ];
  }
}
