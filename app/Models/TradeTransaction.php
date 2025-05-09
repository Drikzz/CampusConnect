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
     * Get the buyer's profile image URL with fallback
     */
    public function getBuyerProfileImageAttribute()
    {
        if (!$this->buyer) {
            return '/storage/imgs/download-Copy.jpg';
        }
        
        $profilePicture = $this->buyer->profile_picture;
        
        if (empty($profilePicture)) {
            return '/storage/imgs/download-Copy.jpg';
        }
        
        // Clean up path format
        if (strpos($profilePicture, 'storage/') === 0) {
            return '/' . $profilePicture;
        } else if (strpos($profilePicture, '/storage/') !== 0) {
            return '/storage/' . $profilePicture;
        }
        
        return $profilePicture;
    }
    
    /**
     * Get the seller's profile image URL with fallback
     */
    public function getSellerProfileImageAttribute()
    {
        if (!$this->seller) {
            return '/storage/imgs/download-Copy.jpg';
        }
        
        $profilePicture = $this->seller->profile_picture;
        
        if (empty($profilePicture)) {
            return '/storage/imgs/download-Copy.jpg';
        }
        
        // Clean up path format
        if (strpos($profilePicture, 'storage/') === 0) {
            return '/' . $profilePicture;
        } else if (strpos($profilePicture, '/storage/') !== 0) {
            return '/storage/' . $profilePicture;
        }
        
        return $profilePicture;
    }
    
    /**
     * Get the seller product's primary image URL with fallback
     */
    public function getProductImageUrlAttribute()
    {
        if (!$this->sellerProduct) {
            return '/storage/imgs/download-Copy.jpg';
        }
        
        $images = $this->sellerProduct->images;
        
        // Handle different image formats
        if (empty($images)) {
            return '/storage/imgs/download-Copy.jpg';
        }
        
        // If it's a JSON string, decode it
        if (is_string($images) && (strpos($images, '[') === 0 || strpos($images, '{') === 0)) {
            try {
                $images = json_decode($images, true);
            } catch (\Exception $e) {
                return '/storage/imgs/download-Copy.jpg';
            }
        }
        
        // Get first image if it's an array
        if (is_array($images) && count($images) > 0) {
            $image = $images[0];
        } else {
            $image = is_string($images) ? $images : null;
        }
        
        if (empty($image)) {
            return '/storage/imgs/download-Copy.jpg';
        }
        
        // Clean up path format
        if (strpos($image, 'storage/') === 0) {
            return '/' . $image;
        } else if (strpos($image, '/storage/') !== 0) {
            return '/storage/' . $image;
        }
        
        return $image;
    }

    /**
     * Get the formatted meetup date
     */
    public function getFormattedMeetupDateAttribute()
    {
        if (!$this->meetup_schedule) {
            return null;
        }
        
        try {
            return $this->meetup_schedule->format('F j, Y \a\t h:i A');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the meetup location name
     */
    public function getMeetupLocationNameAttribute()
    {
        if (!$this->meetupLocation) {
            return 'Location not specified';
        }
        
        if ($this->meetupLocation->location) {
            return $this->meetupLocation->location->name;
        }
        
        return $this->meetupLocation->custom_location ?? 'Location not specified';
    }
}
