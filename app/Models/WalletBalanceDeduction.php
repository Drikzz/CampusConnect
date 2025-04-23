<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletBalanceDeduction extends Model
{
    protected $fillable = [
        'wallet_id',
        'seller_code',
        'transaction_type',
        'transaction_id',
        'transaction_amount',
        'deduction_rate',
        'deduction_amount',
        'previous_balance',
        'new_balance',
        'description',
        'processed_at'
    ];

    protected $casts = [
        'transaction_amount' => 'decimal:2',
        'deduction_rate' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'new_balance' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the wallet that owns the deduction.
     */
    public function wallet()
    {
        return $this->belongsTo(SellerWallet::class, 'wallet_id');
    }
}
