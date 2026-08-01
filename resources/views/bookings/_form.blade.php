@csrf

@php
    $booking = $booking ?? null;
    $selectedGuestId = $selectedGuestId ?? null;
    $availabilityFilters = $availabilityFilters ?? [
        'check_in_date' => '',
        'check_out_date' => '',
        'adults' => '1',
        'children' => '0',
        'building_id' => '',
        'room_id' => '',
    ];
    $availabilitySearched = $availabilitySearched ?? false;
    $roomOptions = $availabilitySearched ? $rooms : $allRooms;

    $itemRows = old('items');
    if (!is_array($itemRows)) {
        $itemRows =
            $booking?->bookingRoomItems
                ->map(function ($item): array {
                    return [
                        'room_id' => (string) $item->room_id,
                        'nightly_rate' => (string) $item->nightly_rate,
                        'adults' => (string) $item->adults,
                        'children' => (string) $item->children,
                        'check_in_date' => $item->check_in_date?->format('Y-m-d') ?? '',
                        'check_out_date' => $item->check_out_date?->format('Y-m-d') ?? '',
                    ];
                })
                ->values()
                ->all() ?? [];
    }

    if ($itemRows === []) {
        $itemRows = [
            [
                'room_id' => '',
                'nightly_rate' => '',
                'adults' => old('adults', $booking?->adults ?? 1),
                'children' => old('children', $booking?->children ?? 0),
                'check_in_date' => old('check_in_date', $booking?->check_in_date?->format('Y-m-d') ?? ''),
                'check_out_date' => old('check_out_date', $booking?->check_out_date?->format('Y-m-d') ?? ''),
            ],
        ];
    }
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label for="guest_id" class="block text-sm font-medium text-slate-700">Guest</label>
        <select id="guest_id" name="guest_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
            <option value="">Select guest</option>
            @foreach ($guests as $guest)
                <option value="{{ $guest->id }}" @selected((string) old('guest_id', $selectedGuestId ?? ($booking?->guest_id ?? '')) === (string) $guest->id)>{{ $guest->full_name }}
                    ({{ $guest->email }})</option>
            @endforeach
        </select>
        @error('guest_id')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="booking_source" class="block text-sm font-medium text-slate-700">Booking Source</label>
        <select id="booking_source" name="booking_source" class="mt-1 w-full rounded-lg border-slate-300 text-sm"
            required>
            @foreach ($sources as $source)
                <option value="{{ $source }}" @selected(old('booking_source', $booking?->booking_source ?? 'other') === $source)>{{ $source }}</option>
            @endforeach
        </select>
        @error('booking_source')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="booking_reference" class="block text-sm font-medium text-slate-700">Booking Reference</label>
        <input id="booking_reference" name="booking_reference" type="text"
            value="{{ old('booking_reference', $booking?->booking_reference ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('booking_reference')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="booking_status" class="block text-sm font-medium text-slate-700">Booking Status</label>
        <select id="booking_status" name="booking_status" class="mt-1 w-full rounded-lg border-slate-300 text-sm"
            required>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('booking_status', $booking?->booking_status ?? 'pending') === $status)>{{ str_replace('_', ' ', $status) }}
                </option>
            @endforeach
        </select>
        @error('booking_status')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="check_in_date" class="block text-sm font-medium text-slate-700">Check In Date</label>
        <input id="check_in_date" name="check_in_date" type="date"
            value="{{ old('check_in_date', $booking?->check_in_date?->format('Y-m-d') ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('check_in_date')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="check_out_date" class="block text-sm font-medium text-slate-700">Check Out Date</label>
        <input id="check_out_date" name="check_out_date" type="date"
            value="{{ old('check_out_date', $booking?->check_out_date?->format('Y-m-d') ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('check_out_date')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="adults" class="block text-sm font-medium text-slate-700">Adults</label>
        <input id="adults" name="adults" type="number" min="1"
            value="{{ old('adults', $booking?->adults ?? 1) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"
            required>
        @error('adults')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="children" class="block text-sm font-medium text-slate-700">Children</label>
        <input id="children" name="children" type="number" min="0"
            value="{{ old('children', $booking?->children ?? 0) }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('children')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="subtotal" class="block text-sm font-medium text-slate-700">Subtotal</label>
        <input id="subtotal" name="subtotal" type="number" min="0" step="0.01"
            value="{{ old('subtotal', $booking?->subtotal ?? 0) }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('subtotal')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="discount" class="block text-sm font-medium text-slate-700">Discount</label>
        <input id="discount" name="discount" type="number" min="0" step="0.01"
            value="{{ old('discount', $booking?->discount ?? 0) }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('discount')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tax" class="block text-sm font-medium text-slate-700">Tax</label>
        <input id="tax" name="tax" type="number" min="0" step="0.01"
            value="{{ old('tax', $booking?->tax ?? 0) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"
            required>
        @error('tax')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="total_amount" class="block text-sm font-medium text-slate-700">Total Amount</label>
        <input id="total_amount" name="total_amount" type="number" min="0" step="0.01"
            value="{{ old('total_amount', $booking?->total_amount ?? 0) }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('total_amount')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="special_requests" class="block text-sm font-medium text-slate-700">Special Requests</label>
        <textarea id="special_requests" name="special_requests" rows="2"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm">{{ old('special_requests', $booking?->special_requests ?? '') }}</textarea>
        @error('special_requests')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="internal_notes" class="block text-sm font-medium text-slate-700">Internal Notes</label>
        <textarea id="internal_notes" name="internal_notes" rows="2"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm">{{ old('internal_notes', $booking?->internal_notes ?? '') }}</textarea>
        @error('internal_notes')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<section class="mt-6 rounded-xl border border-slate-200 p-4">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-900">Booking Room Items</h3>
        <button type="button" id="add-room-item"
            class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Add
            Room Item</button>
    </div>

    @if ($availabilitySearched)
        <p class="mt-2 text-xs text-slate-500">Availability search applied. Room options below are filtered to
            available rooms ({{ $rooms->count() }} found).</p>
    @endif

    <div class="mt-3 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-xs">
            <thead class="bg-slate-50 text-left uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-2 py-2">Room</th>
                    <th class="px-2 py-2">Nightly Rate</th>
                    <th class="px-2 py-2">Adults</th>
                    <th class="px-2 py-2">Children</th>
                    <th class="px-2 py-2">Check In</th>
                    <th class="px-2 py-2">Check Out</th>
                    <th class="px-2 py-2"></th>
                </tr>
            </thead>
            <tbody id="room-items-body" class="divide-y divide-slate-100 text-slate-700">
                @foreach ($itemRows as $index => $item)
                    <tr class="room-item-row">
                        <td class="px-2 py-2">
                            <select name="items[{{ $index }}][room_id]"
                                class="w-full rounded-lg border-slate-300 text-xs" required>
                                <option value="">Select room</option>
                                @foreach ($roomOptions as $room)
                                    <option value="{{ $room->id }}" @selected((string) ($item['room_id'] ?? '') === (string) $room->id)>
                                        {{ $room->code }} - {{ $room->name }}</option>
                                @endforeach
                            </select>
                            @error('items.' . $index . '.room_id')
                                <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                            @enderror
                        </td>
                        <td class="px-2 py-2">
                            <input type="number" name="items[{{ $index }}][nightly_rate]" min="0"
                                step="0.01" value="{{ $item['nightly_rate'] ?? '' }}"
                                class="w-full rounded-lg border-slate-300 text-xs" required>
                            @error('items.' . $index . '.nightly_rate')
                                <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                            @enderror
                        </td>
                        <td class="px-2 py-2">
                            <input type="number" name="items[{{ $index }}][adults]" min="1"
                                value="{{ $item['adults'] ?? 1 }}" class="w-full rounded-lg border-slate-300 text-xs"
                                required>
                            @error('items.' . $index . '.adults')
                                <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                            @enderror
                        </td>
                        <td class="px-2 py-2">
                            <input type="number" name="items[{{ $index }}][children]" min="0"
                                value="{{ $item['children'] ?? 0 }}"
                                class="w-full rounded-lg border-slate-300 text-xs" required>
                            @error('items.' . $index . '.children')
                                <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                            @enderror
                        </td>
                        <td class="px-2 py-2">
                            <input type="date" name="items[{{ $index }}][check_in_date]"
                                value="{{ $item['check_in_date'] ?? '' }}"
                                class="w-full rounded-lg border-slate-300 text-xs" required>
                            @error('items.' . $index . '.check_in_date')
                                <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                            @enderror
                        </td>
                        <td class="px-2 py-2">
                            <input type="date" name="items[{{ $index }}][check_out_date]"
                                value="{{ $item['check_out_date'] ?? '' }}"
                                class="w-full rounded-lg border-slate-300 text-xs" required>
                            @error('items.' . $index . '.check_out_date')
                                <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                            @enderror
                        </td>
                        <td class="px-2 py-2">
                            <button type="button"
                                class="remove-room-item rounded-lg border border-rose-300 px-2 py-1 text-[11px] font-semibold text-rose-700 hover:bg-rose-50">Remove</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @error('items')
        <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
    @enderror
</section>

<div class="mt-6 flex items-center gap-3">
    <button type="submit"
        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Save</button>
    <a href="{{ route('bookings.index') }}"
        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Cancel</a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addButton = document.getElementById('add-room-item');
        const tbody = document.getElementById('room-items-body');

        if (!addButton || !tbody) {
            return;
        }

        const buildRow = (index) => {
            const row = document.createElement('tr');
            row.className = 'room-item-row';

            const roomOptions = @json($roomOptions->map(fn($room) => ['id' => $room->id, 'label' => $room->code . ' - ' . $room->name])->values());
            const roomOptionHtml = ['<option value="">Select room</option>']
                .concat(roomOptions.map((option) =>
                    `<option value="${option.id}">${option.label}</option>`))
                .join('');

            row.innerHTML = `
                <td class="px-2 py-2"><select name="items[${index}][room_id]" class="w-full rounded-lg border-slate-300 text-xs" required>${roomOptionHtml}</select></td>
                <td class="px-2 py-2"><input type="number" name="items[${index}][nightly_rate]" min="0" step="0.01" class="w-full rounded-lg border-slate-300 text-xs" required></td>
                <td class="px-2 py-2"><input type="number" name="items[${index}][adults]" min="1" value="1" class="w-full rounded-lg border-slate-300 text-xs" required></td>
                <td class="px-2 py-2"><input type="number" name="items[${index}][children]" min="0" value="0" class="w-full rounded-lg border-slate-300 text-xs" required></td>
                <td class="px-2 py-2"><input type="date" name="items[${index}][check_in_date]" class="w-full rounded-lg border-slate-300 text-xs" required></td>
                <td class="px-2 py-2"><input type="date" name="items[${index}][check_out_date]" class="w-full rounded-lg border-slate-300 text-xs" required></td>
                <td class="px-2 py-2"><button type="button" class="remove-room-item rounded-lg border border-rose-300 px-2 py-1 text-[11px] font-semibold text-rose-700 hover:bg-rose-50">Remove</button></td>
            `;

            return row;
        };

        addButton.addEventListener('click', function() {
            const index = tbody.querySelectorAll('tr.room-item-row').length;
            tbody.appendChild(buildRow(index));
        });

        tbody.addEventListener('click', function(event) {
            const target = event.target;
            if (!(target instanceof HTMLElement) || !target.classList.contains('remove-room-item')) {
                return;
            }

            const rows = tbody.querySelectorAll('tr.room-item-row');
            if (rows.length <= 1) {
                return;
            }

            target.closest('tr.room-item-row')?.remove();
        });
    });
</script>
