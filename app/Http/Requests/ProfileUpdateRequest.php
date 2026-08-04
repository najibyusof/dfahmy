<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $telegramChatId = $this->input('telegram_chat_id');

        $this->merge([
            'telegram_chat_id' => is_string($telegramChatId)
                ? ($trimmed = trim($telegramChatId)) !== '' ? $trimmed : null
                : $telegramChatId,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'telegram_chat_id' => ['nullable', 'string', 'max:255', 'regex:/^-?\d+$/'],
        ];
    }
}
