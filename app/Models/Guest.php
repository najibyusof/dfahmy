<?php

namespace App\Models;

use Database\Factories\GuestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'user_id',
    'full_name',
    'email',
    'phone_number',
    'identification_number',
    'address',
    'nationality',
    'emergency_contact_name',
    'emergency_contact_phone',
    'notes',
])]
class Guest extends Model
{
        /**
         * @return BelongsTo<User, $this>
         */
        public function user(): BelongsTo
        {
            return $this->belongsTo(User::class);
        }

    /** @use HasFactory<GuestFactory> */
    use HasFactory, Notifiable;

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
