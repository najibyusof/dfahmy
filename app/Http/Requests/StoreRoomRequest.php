<?php

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoomRequest extends FormRequest
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
            'building_id' => ['required', 'integer', 'exists:buildings,id'],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:30', 'unique:rooms,code'],
            'floor' => ['required', 'integer', 'min:1', 'max:30'],
            'room_type' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', Rule::in(Room::STATUSES)],
            'base_nightly_rate' => ['required', 'numeric', 'min:0'],
            'maximum_guests' => ['required', 'integer', 'min:1', 'max:30'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
