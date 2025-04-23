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
        'status',
        'notes',
        'meetup_schedule',
        'meetup_day',
        'preferred_time',
        'wallet_deduction_processed'
    ];

    protected $casts = [
        'additional_cash' => 'decimal:2',
        'meetup_schedule' => 'datetime',
        'wallet_deduction_processed' => 'boolean',
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
        return $this->hasMany(TradeOfferedItem::class, 'trade_transaction_id');
    }

    /**
     * Get the negotiation messages for this trade.
     */
    public function negotiations()
    {
        return $this->hasMany(TradeNegotiation::class, 'trade_transaction_id');
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
        return $this->belongsTo(MeetupLocation::class, 'meetup_location_id');
    }

    /**
     * Get the formatted meetup schedule.
     */
    public function getFormattedMeetupDateAttribute()
    {
        if (!$this->meetup_schedule) {
            return null;
        }
        return $this->meetup_schedule->format('F j, Y \a\t h:i A');
    }

    /**
     * Get the meetup location name.
     */
    public function getMeetupLocationNameAttribute()
    {
        return $this->meetupLocation?->location?->name ?? $this->meetupLocation?->custom_location ?? null;
    }

    /**
     * Get wallet transactions associated with this trade
     */
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class, 'reference_id', 'id')
            ->where(function($query) {
                $query->where('reference_type', 'order')
                      ->where('description', 'like', '%trade%');
            })
            ->where('type', 'debit');
    }
}
