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
        'meetup_location_id',  // ✓ This is correctly defined
        'additional_cash',
        'notes',
        'status',
        'meetup_schedule',     // ✓ This is correctly defined
    ];
    
    protected $casts = [
        'meetup_schedule' => 'datetime',  // ✓ Properly cast to datetime
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
     * Get the messages for this trade.
     */
    public function messages()
    {
        return $this->hasMany(TradeMessage::class);
    }
    
    /**
     * Get the meetup location for this trade.
     */
    public function meetupLocation()
    {
        return $this->belongsTo(MeetupLocation::class, 'meetup_location_id');  // ✓ Correct relationship
    }

    /**
     * Get the formatted meetup schedule.
     */
    public function getFormattedMeetupDateAttribute()
    {
        if (!$this->meetup_schedule) {
            return null;
        }
        return $this->meetup_schedule->format('F j, Y \a\t h:i A');  // ✓ Formats the date correctly
    }

    /**
     * Get the meetup location name.
     */
    public function getMeetupLocationNameAttribute()
    {
        return $this->meetupLocation?->location?->name ?? $this->meetupLocation?->custom_location ?? null;  // ✓ Gets location name
    }
}
