<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'assigned_to_user_id',
    'room_id',
    'booking_id',
    'room_label',
    'task_type',
    'priority',
    'due_date',
    'status',
    'notes',
    'checklist_notes',
    'completed_at',
])]
class HousekeepingTask extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pending',
        'in_progress',
        'completed',
        'cancelled',
    ];

    public const HOUSEKEEPER_ALLOWED_STATUSES = [
        'pending',
        'in_progress',
        'completed',
    ];

    public const TASK_TYPES = [
        'checkout_cleaning',
        'deep_cleaning',
        'maintenance',
        'inspection',
        'other',
    ];

    public const PRIORITIES = [
        'low',
        'medium',
        'high',
        'urgent',
    ];

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * @return BelongsTo<Room, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }
}
