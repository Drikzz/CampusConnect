<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'buyer_id',
        'seller_code',
        'address',
        'delivery_estimate',
        'phone',
        'email',
        'sub_total',
        'status',
        'payment_method'
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_code', 'seller_code');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Helper scopes for filtering orders
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'Processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'Completed');
    }
}
