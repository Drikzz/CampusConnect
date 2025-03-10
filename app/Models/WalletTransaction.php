<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class WalletTransaction extends Model
{
  use SoftDeletes, HasUlids;

  protected $fillable = [
    'user_id', // Add this
    'seller_code',
    'type',
    'amount',
    'previous_balance',
    'new_balance',
    'reference_type',
    'reference_id', // ID of the order, verification request, etc.
    'status',
    'description',
    'verification_type', // e.g., 'seller_activation'
    'verification_data', // Stores ID selfie, WMSU email, agreement status (JSON)
    'receipt_path',
    'processed_at',
    'processed_by',
    'remarks'
  ];

  protected $casts = [
    'amount' => 'decimal:2',
    'previous_balance' => 'decimal:2',
    'new_balance' => 'decimal:2',
    'processed_at' => 'datetime',
    'verification_data' => 'array' // Add this line
  ];

  public static $remarks = [
    'verification_rejected' => "ID is not clear, re-upload required.",
    'withdrawal_rejected' => "Bank details incorrect.",
    'transaction_failed' => "Payment failed due to insufficient funds.",
  ];

  public static $descriptions = [
    'verification_fee' => "Verification request fee for seller activation.",
    'order_deduction' => "Fee deducted for order processing.",
    'refill' => "User added funds via GCash.",
  ];

  public function wallet()
  {
    return $this->belongsTo(SellerWallet::class, 'seller_code', 'seller_code');
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function processedBy()
  {
    return $this->belongsTo(User::class, 'processed_by');
  }

  public function getReceiptUrlAttribute()
  {
    return $this->receipt_path ? asset('storage/' . $this->receipt_path) : null;
  }

  /**
   * Get the columns that should receive a unique identifier.
   *
   * @return array
   */
  public function uniqueIds()
  {
    return ['reference_id'];
  }
}
