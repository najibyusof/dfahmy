<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'enabled'])]
class OperationAlertSetting extends Model
{
    public const TELEGRAM_KEYS = [
        'telegram_new_booking',
        'telegram_booking_cancellation',
        'telegram_check_in',
        'telegram_check_out',
        'telegram_overdue_payment_outstanding_balance',
        'telegram_urgent_housekeeping_task',
        'telegram_urgent_maintenance_request',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }
}
