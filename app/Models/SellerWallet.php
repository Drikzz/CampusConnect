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
}
