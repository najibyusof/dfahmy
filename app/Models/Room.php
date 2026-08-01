<?php

namespace App\Models;

use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'building_id',
    'name',
    'code',
    'floor',
    'room_type',
    'status',
    'base_nightly_rate',
    'maximum_guests',
    'notes',
    'is_active',
])]
class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory;
    use SoftDeletes;

    public const STATUSES = [
        'available',
        'occupied',
        'reserved',
        'cleaning',
        'maintenance',
        'out_of_service',
    ];

    /**
     * @return BelongsTo<Building, $this>
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * @return HasMany<RoomBed, $this>
     */
    public function beds(): HasMany
    {
        return $this->hasMany(RoomBed::class);
    }

    /**
     * @return HasMany<HousekeepingTask, $this>
     */
    public function housekeepingTasks(): HasMany
    {
        return $this->hasMany(HousekeepingTask::class);
    }

    protected function casts(): array
    {
        return [
            'base_nightly_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
