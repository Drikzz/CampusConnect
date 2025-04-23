<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'reviewer_id',
        'seller_code',
        'rating',
        'review',
        'is_verified_purchase',
        'order_id',
    ];

    /**
     * Get the reviewer that wrote the review.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the seller being reviewed.
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_code', 'seller_code');
    }

    /**
     * Get the order associated with the review.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * Alias relationship for the reviewer as buyer.
     * This ensures compatibility with code expecting a 'buyer' relationship.
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
