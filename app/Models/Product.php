<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'status' => 'Active', // Set default status
    ];

    protected $fillable = [
        'name',
        'description',
        'price',
        'discount',
        'discounted_price', // Make sure this matches your database column name exactly
        'images',
        'stock',
        'seller_code',
        'category_id',
        'is_buyable',
        'is_tradable',
        'status',
        'old_attributes'
    ];

    protected $casts = [
        'images' => 'array',
        'is_buyable' => 'boolean',
        'is_tradable' => 'boolean',
        'old_attributes' => 'json',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_code', 'seller_code');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Helper method to check if product is available
    public function isAvailable(): bool
    {
        return $this->stock > 0;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Remove this method since we're not using trade_method anymore
    // public function tradeMethod()
    // {
    //     return $this->belongsTo(tradeMethod::class);
    // }

    public function getMainImageAttribute()
    {
        return $this->images[0];
    }

    public function images(): HasMany
    {
        return $this->hasMany(Product::class, 'images');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getImageAttribute($value)
    {
        return asset('storage/' . $value);
    }

    // public function variants(): HasMany
    // {
    //     return $this->hasMany(ProductVariant::class);
    // }
}
