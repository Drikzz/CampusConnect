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
        'payment_method',
        'meetup_location_id',
        'meetup_schedule',
        'meetup_notes',
        'delivery_proof',
        'dispute_reason',
        'dispute_status',
        'dispute_resolution',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by',
        'wallet_deduction_processed'
    ];

    protected $casts = [
        'meetup_schedule' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'accepted_at' => 'datetime',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'wallet_deduction_processed' => 'boolean',
    ];

    // Add status constants for better maintainability
    const STATUS_PENDING = 'Pending';
    const STATUS_ACCEPTED = 'Accepted';
    const STATUS_MEETUP_SCHEDULED = 'Meetup Scheduled';
    const STATUS_DELIVERED = 'Delivered';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_CANCELLED = 'Cancelled';
    const STATUS_DISPUTED = 'Disputed';

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

    public function meetup_location(): BelongsTo
    {
        return $this->belongsTo(MeetupLocation::class, 'meetup_location_id');
    }

    // Alias method for consistency in naming
    public function meetupLocation(): BelongsTo
    {
        return $this->meetup_location();
    }

    // Add status check methods
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isMeetupScheduled(): bool
    {
        return $this->status === self::STATUS_MEETUP_SCHEDULED;
    }

    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isDisputed(): bool
    {
        return $this->status === self::STATUS_DISPUTED;
    }

    // Add status transition methods
    public function markAsAccepted()
    {
        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'accepted_at' => now()
        ]);
    }

    public function markAsDelivered()
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_at' => now()
        ]);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now()
        ]);
    }

    public function cancel($reason, $cancelledBy)
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
            'cancelled_by' => $cancelledBy
        ]);
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

    public function scopeAccepted($query)
    {
        return $query->where('status', 'Accepted');
    }

    public function scopeMeetupScheduled($query)
    {
        return $query->where('status', 'Meetup Scheduled');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'Delivered');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'Cancelled');
    }

    public function scopeDisputed($query)
    {
        return $query->where('status', 'Disputed');
    }

    /**
     * Get wallet transactions associated with this order
     */
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class, 'reference_id', 'id')
            ->where('reference_type', 'order')
            ->where('description', 'like', 'Platform fee%')
            ->where('type', 'debit');
    }
}
