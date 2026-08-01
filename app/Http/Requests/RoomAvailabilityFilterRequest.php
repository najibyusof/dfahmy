<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoomAvailabilityFilterRequest extends FormRequest
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
            'building_id' => ['nullable', 'integer', 'exists:buildings,id'],
            'on_date' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'building_id' => isset($validated['building_id']) ? (string) $validated['building_id'] : '',
            'on_date' => isset($validated['on_date']) ? (string) $validated['on_date'] : now()->toDateString(),
        ];
    }
}
