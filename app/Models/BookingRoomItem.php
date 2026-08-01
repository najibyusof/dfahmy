<?php

namespace App\Models;

use Database\Factories\BookingRoomItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'booking_id',
    'bookable_unit_id',
    'bookable_unit_name',
    'bookable_unit_code',
    'booking_type',
    'included_rooms_snapshot',
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

    /**
     * @return BelongsTo<BookableUnit, $this>
     */
    public function bookableUnit(): BelongsTo
    {
        return $this->belongsTo(BookableUnit::class);
    }

    /**
     * @return BelongsToMany<Room, $this>
     */
    public function includedRooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'booking_room_item_rooms')->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'nightly_rate' => 'decimal:2',
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'included_rooms_snapshot' => 'array',
        ];
    }
}
