<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SellerWallet extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'seller_code',
    'balance',
    'is_activated',
    'status',
    'activated_at'
  ];

  protected $casts = [
    'is_activated' => 'boolean',
    'activated_at' => 'datetime',
    'balance' => 'decimal:2'
  ];

  public function seller()
  {
    return $this->belongsTo(User::class, 'seller_code', 'seller_code');
  }

  public function transactions()
  {
    return $this->hasMany(WalletTransaction::class, 'seller_code', 'seller_code');
  }
}
