<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
  protected $fillable = [
    'seller_code',
    'type',
    'amount',
    'previous_balance',
    'new_balance',
    'reference_type',
    'reference_id',
    'status',
    'description'
  ];

  protected $casts = [
    'amount' => 'decimal:2',
    'previous_balance' => 'decimal:2',
    'new_balance' => 'decimal:2'
  ];

  public function wallet()
  {
    return $this->belongsTo(SellerWallet::class, 'seller_code', 'seller_code');
  }
}
