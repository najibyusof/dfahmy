<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
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
        /** @var Payment $payment */
        $payment = $this->route('payment');

        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'receipt_number' => ['required', 'string', 'max:60', Rule::unique('payments', 'receipt_number')->ignore($payment->id)],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999.99'],
            'payment_method' => ['required', Rule::in(Payment::METHODS)],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'payment_status' => ['required', Rule::in(Payment::STATUSES)],
            'received_by_user_id' => [
                'required',
                'integer',
                'exists:users,id',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $actor = $this->user();
                    if ($actor === null) {
                        return;
                    }

                    if ((int) $value !== (int) $actor->id && ! $actor->can('users.manage')) {
                        $fail('You can only record payments under your own account.');
                    }
                },
            ],
            'notes' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
