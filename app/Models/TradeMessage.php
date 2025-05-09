<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeMessage extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'trade_transaction_id',
        'sender_id',
        'message',
        'read_at'
    ];
    
    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Get the trade transaction that owns this message.
     */
    public function tradeTransaction()
    {
        return $this->belongsTo(TradeTransaction::class);
    }
    
    /**
     * Get the user who sent this message.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Create a new message for a trade
     * 
     * @param int $tradeId
     * @param int $senderId
     * @param string $message
     * @return static
     */
    public static function createMessage($tradeId, $senderId, $message)
    {
        return static::create([
            'trade_transaction_id' => $tradeId,
            'sender_id' => $senderId,
            'message' => $message,
            'read_at' => null // New messages start unread
        ]);
    }
}
