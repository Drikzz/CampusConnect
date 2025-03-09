<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeNegotiation extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'trade_transaction_id',
        'user_id',
        'message',
    ];
    
    /**
     * Get the trade transaction that this negotiation belongs to.
     */
    public function tradeTransaction()
    {
        return $this->belongsTo(TradeTransaction::class);
    }
    
    /**
     * Get the user that created this negotiation message.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
