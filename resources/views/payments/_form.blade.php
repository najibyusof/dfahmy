@csrf

@php
    $payment = $payment ?? null;
    $selectedBookingId = $selectedBookingId ?? null;
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label for="booking_id" class="block text-sm font-medium text-slate-700">Booking</label>
        <select id="booking_id" name="booking_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
            <option value="">Select booking</option>
            @foreach ($bookings as $booking)
                <option value="{{ $booking->id }}" @selected((string) old('booking_id', $selectedBookingId ?? ($payment?->booking_id ?? '')) === (string) $booking->id)>
                    {{ $booking->booking_reference }} - {{ $booking->guest->full_name }} (RM
                    {{ number_format((float) $booking->total_amount, 2) }})
                </option>
            @endforeach
        </select>
        @error('booking_id')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="receipt_number" class="block text-sm font-medium text-slate-700">Receipt Number</label>
        <input id="receipt_number" name="receipt_number" type="text"
            value="{{ old('receipt_number', $payment?->receipt_number ?? ($suggestedReceiptNumber ?? '')) }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('receipt_number')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="payment_date" class="block text-sm font-medium text-slate-700">Payment Date</label>
        <input id="payment_date" name="payment_date" type="date"
            value="{{ old('payment_date', $payment?->payment_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('payment_date')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="amount" class="block text-sm font-medium text-slate-700">Amount</label>
        <input id="amount" name="amount" type="number" min="0.01" step="0.01"
            value="{{ old('amount', $payment?->amount ?? '') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"
            required>
        @error('amount')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="payment_method" class="block text-sm font-medium text-slate-700">Payment Method</label>
        <select id="payment_method" name="payment_method" class="mt-1 w-full rounded-lg border-slate-300 text-sm"
            required>
            @foreach ($paymentMethods as $paymentMethod)
                <option value="{{ $paymentMethod }}" @selected(old('payment_method', $payment?->payment_method ?? 'cash') === $paymentMethod)>
                    {{ str_replace('_', ' ', $paymentMethod) }}
                </option>
            @endforeach
        </select>
        @error('payment_method')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="payment_status" class="block text-sm font-medium text-slate-700">Payment Status</label>
        <select id="payment_status" name="payment_status" class="mt-1 w-full rounded-lg border-slate-300 text-sm"
            required>
            @foreach ($paymentStatuses as $paymentStatus)
                <option value="{{ $paymentStatus }}" @selected(old('payment_status', $payment?->payment_status ?? 'paid') === $paymentStatus)>
                    {{ str_replace('_', ' ', $paymentStatus) }}
                </option>
            @endforeach
        </select>
        @error('payment_status')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="reference_number" class="block text-sm font-medium text-slate-700">Reference Number</label>
        <input id="reference_number" name="reference_number" type="text"
            value="{{ old('reference_number', $payment?->reference_number ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm">
        @error('reference_number')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="received_by_user_id" class="block text-sm font-medium text-slate-700">Received By User</label>
        <select id="received_by_user_id" name="received_by_user_id"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
            <option value="">Select user</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected((string) old('received_by_user_id', $payment?->received_by_user_id ?? auth()->id()) === (string) $user->id)>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
        @error('received_by_user_id')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-slate-700">Notes</label>
        <textarea id="notes" name="notes" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm">{{ old('notes', $payment?->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit"
        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Save
        Payment</button>
    <a href="{{ route('payments.index') }}"
        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Cancel</a>
</div>
