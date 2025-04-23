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
        'description',
        'condition',
        'images'
    ];

    // Add custom accessor and mutator to properly handle images as JSON
    protected $casts = [
        'images' => 'array'
    ];

    // Ensure we're always getting an array, even if images is null
    public function getImagesAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        // Try to decode JSON string
        try {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [$value];
        } catch (\Exception $e) {
            return [$value];
        }
    }

    // Ensure we're always storing a clean JSON string
    public function setImagesAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['images'] = json_encode($value, JSON_UNESCAPED_SLASHES);
        } else if (is_string($value) && (strpos($value, '[') === 0 || strpos($value, '{') === 0)) {
            // It's already a JSON string, leave it as is
            $this->attributes['images'] = $value;
        } else {
            // It's a single value, wrap in array and encode
            $this->attributes['images'] = json_encode([$value], JSON_UNESCAPED_SLASHES);
        }
    }

    public function tradeTransaction()
    {
        return $this->belongsTo(TradeTransaction::class);
    }
}
