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
    'activated_at',
    'id_path'
  ];

  protected $casts = [
    'balance' => 'decimal:2',
    'is_activated' => 'boolean',
    'activated_at' => 'datetime'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function transactions()
  {
    return $this->hasMany(WalletTransaction::class, 'user_id', 'user_id');
  }
}
