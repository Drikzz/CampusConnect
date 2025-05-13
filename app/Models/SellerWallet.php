<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SellerWallet extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'user_id',
    'seller_code',
    'balance',
    'is_activated',
    'status',
    'activated_at'
  ];

  protected $casts = [
    'balance' => 'decimal:2',
    'is_activated' => 'boolean',
    'activated_at' => 'datetime'
  ];

  // Define ENUM statuses for easy reference
  public const STATUS_PENDING = 'pending';
  public const STATUS_PENDING_APPROVAL = 'pending_approval';
  public const STATUS_ACTIVE = 'active';
  public const STATUS_SUSPENDED = 'suspended';

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function transactions()
  {
    return $this->hasMany(WalletTransaction::class, 'seller_code', 'seller_code');
  }

  public function deductBalance(float $amount): bool
  {
    // Log the current balance and deduction amount for debugging
    \Log::info('Attempting to deduct balance', [
        'current_balance' => $this->balance,
        'deduction_amount' => $amount,
        'seller_code' => $this->seller_code
    ]);

    // Ensure the deduction amount is reasonable
    if ($amount <= 0) {
        \Log::warning('Invalid deduction amount', ['amount' => $amount]);
        return false; // Invalid deduction amount
    }

    // Check if the wallet has sufficient balance
    if ($this->balance < $amount) {
        \Log::warning('Insufficient balance for deduction', [
            'current_balance' => $this->balance,
            'deduction_amount' => $amount
        ]);
        return false; // Insufficient balance
    }

    // Store the previous balance for verification
    $previousBalance = $this->balance;
    
    // Deduct the amount and ensure precision
    $this->balance = round($this->balance - $amount, 2);
    
    // Double-check to ensure we're not setting to zero incorrectly
    if ($this->balance == 0 && $previousBalance > $amount) {
        \Log::error('Potential incorrect zero balance detected', [
            'previous_balance' => $previousBalance,
            'deduction_amount' => $amount,
            'calculated_balance' => $this->balance
        ]);
        $this->balance = round($previousBalance - $amount, 2);
    }

    $saved = $this->save();
    
    \Log::info('Balance after deduction', [
        'previous_balance' => $previousBalance, 
        'deduction_amount' => $amount,
        'new_balance' => $this->balance,
        'save_result' => $saved
    ]);
    
    return $saved;
  }
}
