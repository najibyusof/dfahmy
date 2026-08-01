<?php

namespace App\Http\Requests;

use App\Models\BookableUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookableUnitRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:60', Rule::unique('bookable_units', 'code')],
            'description' => ['nullable', 'string', 'max:4000'],
            'booking_type' => ['required', Rule::in(BookableUnit::TYPES)],
            'base_nightly_rate' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'maximum_guests' => ['required', 'integer', 'min:1', 'max:200'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'room_ids' => ['required', 'array', 'min:1'],
            'room_ids.*' => ['required', 'integer', 'exists:rooms,id', 'distinct'],
        ];
    }
}
