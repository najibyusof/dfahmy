<?php

namespace App\Http\Requests;

use App\Models\HousekeepingTask;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HousekeepingTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(HousekeepingTask::HOUSEKEEPER_ALLOWED_STATUSES)],
            'checklist_notes' => ['nullable', 'string'],
        ];
    }
}
