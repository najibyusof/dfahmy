<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleAuditFilterRequest extends FormRequest
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
            'actor' => ['nullable', 'string', 'max:100'],
            'target' => ['nullable', 'string', 'max:100'],
            'from_role' => ['nullable', 'string', Rule::in(['Super Admin', 'Manager', 'Receptionist', 'Housekeeper'])],
            'to_role' => ['nullable', 'string', Rule::in(['Super Admin', 'Manager', 'Receptionist', 'Housekeeper'])],
            'from_date' => ['nullable', 'date', 'before_or_equal:to_date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'sort' => ['nullable', 'string', Rule::in([
                'created_at_desc',
                'created_at_asc',
                'actor_asc',
                'actor_desc',
                'target_asc',
                'target_desc',
                'from_role_asc',
                'from_role_desc',
                'to_role_asc',
                'to_role_desc',
            ])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'from_date.before_or_equal' => 'The start date must be before or equal to the end date.',
            'to_date.after_or_equal' => 'The end date must be after or equal to the start date.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'actor' => $validated['actor'] ?? null,
            'target' => $validated['target'] ?? null,
            'from_role' => $validated['from_role'] ?? null,
            'to_role' => $validated['to_role'] ?? null,
            'from_date' => $validated['from_date'] ?? null,
            'to_date' => $validated['to_date'] ?? null,
            'sort' => $validated['sort'] ?? 'created_at_desc',
            'per_page' => (int) ($validated['per_page'] ?? 10),
        ];
    }
}
