<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
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
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    /**
     * @return array{from: string, to: string}
     */
    public function dateRange(): array
    {
        $validated = $this->validated();

        $from = (string) ($validated['from'] ?? now()->startOfMonth()->toDateString());
        $to = (string) ($validated['to'] ?? now()->toDateString());

        return ['from' => $from, 'to' => $to];
    }
}
