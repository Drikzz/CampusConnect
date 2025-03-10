<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeOfferedItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'trade_transaction_id',
        'name',
        'quantity',
        'estimated_value',
        'images',
        'description',
    ];
    
    /**
     * Get the trade transaction that this item belongs to.
     */
    public function tradeTransaction()
    {
        return $this->belongsTo(TradeTransaction::class);
    }
    
    /**
     * Get the images as an array.
     */
    public function getImagesAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true);
        }
        
        return $value;
    }
}
