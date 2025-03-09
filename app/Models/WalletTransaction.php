<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletTransaction extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'user_id',
    'amount',
    'previous_balance',
    'new_balance',
    'reference_type',
    'reference_id',
    'status',
    'description',
    'receipt_path',
    'processed_at',
    'processed_by'
  ];

  protected $casts = [
    'amount' => 'decimal:2',
    'previous_balance' => 'decimal:2',
    'new_balance' => 'decimal:2',
    'processed_at' => 'datetime'
  ];

  public function wallet()
  {
    return $this->belongsTo(SellerWallet::class, 'user_id', 'user_id');
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function processor()
  {
    return $this->belongsTo(User::class, 'processed_by');
  }

  public function getReceiptUrlAttribute()
  {
    return $this->receipt_path ? asset('storage/' . $this->receipt_path) : null;
  }
}
