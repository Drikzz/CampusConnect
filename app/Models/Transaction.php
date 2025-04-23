<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 
        'amount',
        'status',
        'user_id',
        'product_id',
        'buyer_id',
        'seller_id',
    ];

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product associated with the transaction.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Get the buyer of the transaction.
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
    
    /**
     * Get the seller of the transaction.
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
