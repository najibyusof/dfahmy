<?php

namespace App\Models;

use Database\Factories\BookableUnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'code',
    'description',
    'booking_type',
    'base_nightly_rate',
    'maximum_guests',
    'is_active',
    'sort_order',
])]
class BookableUnit extends Model
{
    /** @use HasFactory<BookableUnitFactory> */
    use HasFactory;

    public const TYPES = [
        'room',
        'room_group',
        'floor',
        'building',
        'whole_resort',
    ];

    /**
     * @return BelongsToMany<Room, $this>
     */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'bookable_unit_room')->withTimestamps();
    }

    /**
     * @return HasMany<BookingRoomItem, $this>
     */
    public function bookingRoomItems(): HasMany
    {
        return $this->hasMany(BookingRoomItem::class);
    }

    protected function casts(): array
    {
        return [
            'base_nightly_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
