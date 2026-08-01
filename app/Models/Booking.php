<?php

namespace App\Models;

use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'guest_id',
    'booking_reference',
    'check_in_date',
    'check_out_date',
    'adults',
    'children',
    'booking_source',
    'booking_status',
    'special_requests',
    'internal_notes',
    'subtotal',
    'discount',
    'tax',
    'total_amount',
])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    public const STATUSES = [
        'inquiry',
        'pending',
        'confirmed',
        'checked_in',
        'checked_out',
        'cancelled',
        'no_show',
    ];

    public const SOURCES = [
        'walk_in',
        'phone',
        'website',
        'WhatsApp',
        'Agoda',
        'Booking.com',
        'Airbnb',
        'other',
    ];

    public const BLOCKING_STATUSES = [
        'inquiry',
        'pending',
        'confirmed',
        'checked_in',
    ];

    /**
     * @return BelongsTo<Guest, $this>
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * @return HasMany<BookingRoomItem, $this>
     */
    public function bookingRoomItems(): HasMany
    {
        return $this->hasMany(BookingRoomItem::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function totalPaidAmount(): float
    {
        $paidTotal = $this->relationLoaded('payments')
            ? (float) $this->payments
                ->where('payment_status', 'paid')
                ->sum(static fn(Payment $payment): float => (float) $payment->amount)
            : (float) $this->payments()->where('payment_status', 'paid')->sum('amount');

        return round($paidTotal, 2);
    }

    public function outstandingBalanceAmount(): float
    {
        $outstanding = (float) $this->total_amount - $this->totalPaidAmount();

        return round(max($outstanding, 0), 2);
    }

    public function paymentSummaryStatus(): string
    {
        $totalAmount = round((float) $this->total_amount, 2);
        $paidTotal = $this->totalPaidAmount();

        if ($paidTotal <= 0.0) {
            return 'unpaid';
        }

        if ($paidTotal + 0.00001 < $totalAmount) {
            return 'partially_paid';
        }

        if ($paidTotal - $totalAmount > 0.00001) {
            return 'overpaid';
        }

        return 'paid';
    }

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }
}
