<?php

namespace App\Http\Requests;

use App\Models\HousekeepingTask;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHousekeepingTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'booking_id' => ['nullable', 'integer', 'exists:bookings,id'],
            'assigned_to_user_id' => [
                'required',
                'integer',
                'exists:users,id',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $user = User::query()->find((int) $value);
                    if ($user === null || ! $user->can('housekeeping.assigned.view')) {
                        $fail('Assigned user must have housekeeping assignee permission.');
                    }
                },
            ],
            'task_type' => ['required', Rule::in(HousekeepingTask::TASK_TYPES)],
            'priority' => ['required', Rule::in(HousekeepingTask::PRIORITIES)],
            'due_date' => ['required', 'date'],
            'status' => ['required', Rule::in(HousekeepingTask::STATUSES)],
            'notes' => ['nullable', 'string'],
            'checklist_notes' => ['nullable', 'string'],
            'completed_at' => ['nullable', 'date'],
        ];
    }
}
