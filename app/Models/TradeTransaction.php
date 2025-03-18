<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TradeTransaction extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'seller_code',
        'seller_product_id',
        'meetup_location_id',
        'additional_cash',
        'notes',
        'status',
        'meetup_schedule',
    ];
    
    protected $casts = [
        'meetup_schedule' => 'datetime',
    ];
    
    /**
     * Get the buyer associated with the trade.
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
    
    /**
     * Get the seller associated with the trade.
     * First tries seller_id, then falls back to seller_code
     */
    public function seller()
    {
        // If we have a seller_id, use that relationship
        if (!empty($this->seller_id)) {
            return $this->belongsTo(User::class, 'seller_id');
        }
        
        // Otherwise use the seller_code relationship
        return $this->belongsTo(User::class, 'seller_code', 'seller_code');
    }
    
    /**
     * Get the product being traded for.
     */
    public function sellerProduct()
    {
        return $this->belongsTo(Product::class, 'seller_product_id');
    }
    
    /**
     * Get the offered items for this trade.
     */
    public function offeredItems()
    {
        return $this->hasMany(TradeOfferedItem::class);
    }
    
    /**
     * Get the negotiation messages for this trade.
     */
    public function negotiations()
    {
        return $this->hasMany(TradeNegotiation::class);
    }
    
    /**
     * Get the meetup location for this trade.
     */
    public function meetupLocation()
    {
        return $this->belongsTo(MeetupLocation::class);
    }
}
