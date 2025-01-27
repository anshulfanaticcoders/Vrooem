<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'payment_method',
        'transaction_id',
        'amount',
        'payment_status',
        'payment_date'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime'
    ];

    // Define payment status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    // Define payment methods
    public const METHOD_STRIPE = 'stripe';
    public const METHOD_PAYPAL = 'paypal';
    public const METHOD_BANK_TRANSFER = 'bank_transfer';

    // Relationship with Booking
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // Get formatted amount
    public function getFormattedAmountAttribute(): string
    {
        return '€' . number_format($this->amount, 2);
    }

    // Check if payment is completed
    public function isCompleted(): bool
    {
        return $this->payment_status === self::STATUS_COMPLETED;
    }

    // Check if payment is pending
    public function isPending(): bool
    {
        return $this->payment_status === self::STATUS_PENDING;
    }

    // Check if payment failed
    public function isFailed(): bool
    {
        return $this->payment_status === self::STATUS_FAILED;
    }

    // Update payment status
    public function updateStatus(string $status): void
    {
        $this->update(['payment_status' => $status]);
    }

    // Mark payment as completed
    public function markAsCompleted(): void
    {
        $this->updateStatus(self::STATUS_COMPLETED);
    }

    // Mark payment as failed
    public function markAsFailed(): void
    {
        $this->updateStatus(self::STATUS_FAILED);
    }
}