<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
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
        $payload = $this->all();

        return [
            'booking_reference' => ['required', 'string', 'max:40', Rule::unique('bookings', 'booking_reference')],
            'guest_id' => ['required', 'integer', 'exists:guests,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'adults' => ['required', 'integer', 'min:1', 'max:50'],
            'children' => ['required', 'integer', 'min:0', 'max:50'],
            'booking_source' => ['required', Rule::in(Booking::SOURCES)],
            'booking_status' => ['required', Rule::in(Booking::STATUSES)],
            'special_requests' => ['nullable', 'string', 'max:2000'],
            'internal_notes' => ['nullable', 'string', 'max:4000'],
            'subtotal' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'discount' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'tax' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.room_id' => ['required', 'integer', 'exists:rooms,id', 'distinct'],
            'items.*.nightly_rate' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'items.*.adults' => ['required', 'integer', 'min:1', 'max:50'],
            'items.*.children' => ['required', 'integer', 'min:0', 'max:50'],
            'items.*.check_in_date' => ['required', 'date', 'after_or_equal:check_in_date'],
            'items.*.check_out_date' => [
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail) use ($payload): void {
                    if (! preg_match('/^items\.(\d+)\.check_out_date$/', $attribute, $matches)) {
                        return;
                    }

                    $index = (int) $matches[1];
                    $itemCheckOut = (string) $value;
                    $itemCheckIn = (string) data_get($payload, "items.$index.check_in_date", '');
                    $bookingCheckOut = (string) data_get($payload, 'check_out_date', '');

                    if ($itemCheckIn !== '' && $itemCheckOut <= $itemCheckIn) {
                        $fail('Room stay check-out date must be after its check-in date.');
                    }

                    if ($bookingCheckOut !== '' && $itemCheckOut > $bookingCheckOut) {
                        $fail('Room stay check-out date cannot be later than booking check-out date.');
                    }
                },
            ],
        ];
    }
}
