<?php

namespace App\Observers;

use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function created(Model $model): void
    {
        $this->auditLogService->record('create', $model, null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $newValues = $model->getChanges();
        unset($newValues['updated_at']);

        if ($newValues === []) {
            return;
        }

        $oldValues = [];
        if (method_exists($model, 'getPrevious')) {
            $previous = $model->getPrevious();
            foreach (array_keys($newValues) as $key) {
                $oldValues[$key] = $previous[$key] ?? null;
            }
        } else {
            foreach (array_keys($newValues) as $key) {
                $oldValues[$key] = $model->getOriginal($key);
            }
        }

        $this->auditLogService->record('update', $model, $oldValues, $newValues);

        $statusFields = ['status', 'booking_status', 'payment_status'];
        $statusOld = [];
        $statusNew = [];

        foreach ($statusFields as $field) {
            if (array_key_exists($field, $newValues)) {
                $statusOld[$field] = $oldValues[$field] ?? null;
                $statusNew[$field] = $newValues[$field] ?? null;
            }
        }

        if ($statusNew !== []) {
            $this->auditLogService->record('status_change', $model, $statusOld, $statusNew);
        }
    }

    public function deleted(Model $model): void
    {
        $this->auditLogService->record('delete', $model, $model->getOriginal(), null);
    }
}
