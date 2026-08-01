<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomBedsRequest extends FormRequest
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
            'queen_bed_quantity' => ['required', 'integer', 'min:0', 'max:20'],
            'sofa_bed_quantity' => ['required', 'integer', 'min:0', 'max:20'],
        ];
    }
}
