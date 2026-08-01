<?php

namespace App\Models;

use Database\Factories\BookingRoomItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'booking_id',
    'room_id',
    'nightly_rate',
    'adults',
    'children',
    'check_in_date',
    'check_out_date',
])]
class BookingRoomItem extends Model
{
    /** @use HasFactory<BookingRoomItemFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * @return BelongsTo<Room, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    protected function casts(): array
    {
        return [
            'nightly_rate' => 'decimal:2',
            'check_in_date' => 'date',
            'check_out_date' => 'date',
        ];
    }
}
