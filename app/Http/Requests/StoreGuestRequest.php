<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGuestRequest extends FormRequest
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
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('guests', 'email')],
            'phone_number' => ['required', 'string', 'max:30', Rule::unique('guests', 'phone_number')],
            'identification_number' => ['required', 'string', 'max:100', Rule::unique('guests', 'identification_number')],
            'address' => ['nullable', 'string'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Duplicate detected: this email already exists.',
            'phone_number.unique' => 'Duplicate detected: this phone number already exists.',
            'identification_number.unique' => 'Duplicate detected: this identification or passport number already exists.',
        ];
    }
}
