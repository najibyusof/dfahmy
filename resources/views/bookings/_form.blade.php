@csrf

@php
    use Illuminate\Support\Carbon;

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
    $bookableUnits = $bookableUnits ?? collect();
    $allBookableUnits = $allBookableUnits ?? collect();
    $unitOptions = $availabilitySearched ? $bookableUnits : $allBookableUnits;

    $itemRows = old('items');
    if (!is_array($itemRows)) {
        $itemRows =
            $booking?->bookingRoomItems
                ->map(function ($item): array {
                    return [
                        'bookable_unit_id' => $item->bookable_unit_id ? (string) $item->bookable_unit_id : '',
                        'room_id' => (string) $item->room_id,
                        'nightly_rate' => (string) $item->nightly_rate,
                        'adults' => (string) $item->adults,
                        'children' => (string) $item->children,
                        'check_in_date' => $item->check_in_date?->format('Y-m-d') ?? '',
                        'check_out_date' => $item->check_out_date?->format('Y-m-d') ?? '',
                        'included_rooms_snapshot' => $item->included_rooms_snapshot ?? [],
                    ];
                })
                ->values()
                ->all() ?? [];
    }

    if ($itemRows === []) {
        $itemRows = [
            [
                'bookable_unit_id' => '',
                'room_id' => '',
                'nightly_rate' => '',
                'adults' => old('adults', $booking?->adults ?? 1),
                'children' => old('children', $booking?->children ?? 0),
                'check_in_date' => old('check_in_date', $booking?->check_in_date?->format('Y-m-d') ?? ''),
                'check_out_date' => old('check_out_date', $booking?->check_out_date?->format('Y-m-d') ?? ''),
                'included_rooms_snapshot' => [],
            ],
        ];
    }

    $unitMap = [];
    $unitOptionsForJs = [];
    foreach ($allBookableUnits as $unit) {
        $snapshot = $unit->rooms
            ->map(fn($room) => [
                'room_id' => $room->id,
                'room_code' => $room->code,
                'room_name' => $room->name,
            ])
            ->values()
            ->all();

        $unitMap[(string) $unit->id] = [
            'id' => $unit->id,
            'name' => $unit->name,
            'code' => $unit->code,
            'booking_type' => $unit->booking_type,
            'base_nightly_rate' => (float) $unit->base_nightly_rate,
            'maximum_guests' => (int) $unit->maximum_guests,
            'rooms' => $snapshot,
            'rooms_label' => collect($snapshot)->map(fn($r) => ($r['room_code'] ?? 'ROOM') . ' - ' . ($r['room_name'] ?? ''))->implode(', '),
        ];

        $unitOptionsForJs[] = [
            'id' => $unit->id,
            'name' => $unit->name,
            'booking_type' => $unit->booking_type,
        ];
    }

    $availabilityNights = null;
    if (($availabilityFilters['check_in_date'] ?? '') !== '' && ($availabilityFilters['check_out_date'] ?? '') !== '') {
        try {
            $availabilityNights = Carbon::parse($availabilityFilters['check_in_date'])->diffInDays(Carbon::parse($availabilityFilters['check_out_date']));
        } catch (\Throwable $e) {
            $availabilityNights = null;
        }
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
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h3 class="text-sm font-semibold text-slate-900">Booking Unit Items</h3>
        <button type="button" id="add-bookable-unit-item"
            class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Add
            Unit Item</button>
    </div>

    @if ($availabilitySearched)
        <p class="mt-2 text-xs text-slate-500">Availability search applied. Available unit options: {{ $bookableUnits->count() }}.</p>
    @endif

    <div class="mt-3 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-xs">
            <thead class="bg-slate-50 text-left uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-2 py-2">Bookable Unit</th>
                    <th class="px-2 py-2">Included Rooms</th>
                    <th class="px-2 py-2">Nightly Rate</th>
                    <th class="px-2 py-2">Adults</th>
                    <th class="px-2 py-2">Children</th>
                    <th class="px-2 py-2">Check In</th>
                    <th class="px-2 py-2">Check Out</th>
                    <th class="px-2 py-2"></th>
                </tr>
            </thead>
            <tbody id="bookable-unit-items-body" class="divide-y divide-slate-100 text-slate-700">
                @foreach ($itemRows as $index => $item)
                    @php
                        $selectedUnitId = (string) ($item['bookable_unit_id'] ?? '');
                        $selectedUnit = $selectedUnitId !== '' ? ($unitMap[$selectedUnitId] ?? null) : null;
                        $roomsLabel = '';
                        if ($selectedUnit !== null) {
                            $roomsLabel = $selectedUnit['rooms_label'];
                        } else {
                            $snapshot = $item['included_rooms_snapshot'] ?? [];
                            if (is_array($snapshot)) {
                                $roomsLabel = collect($snapshot)->map(fn($room) => ($room['room_code'] ?? 'ROOM') . ' - ' . ($room['room_name'] ?? ''))->implode(', ');
                            }
                        }
                    @endphp
                    <tr class="bookable-unit-item-row">
                        <td class="px-2 py-2">
                            <select name="items[{{ $index }}][bookable_unit_id]"
                                class="bookable-unit-select w-full rounded-lg border-slate-300 text-xs" required>
                                <option value="">Select unit</option>
                                @foreach ($unitOptions as $unit)
                                    <option value="{{ $unit->id }}" @selected($selectedUnitId === (string) $unit->id)>
                                        {{ $unit->name }} ({{ str_replace('_', ' ', $unit->booking_type) }})
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="items[{{ $index }}][room_id]" class="room-id-hidden"
                                value="{{ $item['room_id'] ?? '' }}">
                            @error('items.' . $index . '.bookable_unit_id')
                                <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                            @enderror
                            @error('items.' . $index . '.room_id')
                                <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                            @enderror
                        </td>
                        <td class="px-2 py-2">
                            <p class="included-rooms-text rounded-lg bg-slate-50 px-2 py-1 text-[11px] text-slate-600">{{ $roomsLabel !== '' ? $roomsLabel : '-' }}</p>
                        </td>
                        <td class="px-2 py-2">
                            <input type="number" name="items[{{ $index }}][nightly_rate]" min="0"
                                step="0.01" value="{{ $item['nightly_rate'] ?? '' }}"
                                class="nightly-rate-input w-full rounded-lg border-slate-300 text-xs" required>
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
                                class="remove-bookable-unit-item rounded-lg border border-rose-300 px-2 py-1 text-[11px] font-semibold text-rose-700 hover:bg-rose-50">Remove</button>
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

@if ($availabilitySearched)
    <section class="mt-6 rounded-xl border border-slate-200 p-4">
        <h3 class="text-sm font-semibold text-slate-900">Available Bookable Units</h3>
        <div class="mt-3 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-left uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-2 py-2">Name</th>
                        <th class="px-2 py-2">Type</th>
                        <th class="px-2 py-2">Included Rooms</th>
                        <th class="px-2 py-2">Max Guests</th>
                        <th class="px-2 py-2">Nightly Rate</th>
                        <th class="px-2 py-2">Nights</th>
                        <th class="px-2 py-2">Calculated Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($bookableUnits as $unit)
                        @php
                            $includedRoomList = $unit->rooms->map(fn($room) => $room->code . ' - ' . $room->name)->implode(', ');
                            $nights = $availabilityNights ?? 0;
                            $computedTotal = max(0, $nights) * (float) $unit->base_nightly_rate;
                        @endphp
                        <tr>
                            <td class="px-2 py-2 font-medium text-slate-900">{{ $unit->name }}</td>
                            <td class="px-2 py-2">{{ str_replace('_', ' ', $unit->booking_type) }}</td>
                            <td class="px-2 py-2">{{ $includedRoomList }}</td>
                            <td class="px-2 py-2">{{ $unit->maximum_guests }}</td>
                            <td class="px-2 py-2">RM {{ number_format((float) $unit->base_nightly_rate, 2) }}</td>
                            <td class="px-2 py-2">{{ $availabilityNights ?? '-' }}</td>
                            <td class="px-2 py-2">RM {{ number_format($computedTotal, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-2 py-3 text-center text-slate-500">No bookable units available for the selected criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endif

<div class="mt-6 flex items-center gap-3">
    <button type="submit"
        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Save</button>
    <a href="{{ route('bookings.index') }}"
        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Cancel</a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addButton = document.getElementById('add-bookable-unit-item');
        const tbody = document.getElementById('bookable-unit-items-body');
        const unitMap = @json($unitMap);
        const unitOptions = @json($unitOptionsForJs);

        if (!addButton || !tbody) {
            return;
        }

        const buildUnitOptionsHtml = () => {
            const options = ['<option value="">Select unit</option>'];
            unitOptions.forEach((unit) => {
                options.push(`<option value="${unit.id}">${unit.name} (${String(unit.booking_type).replaceAll('_', ' ')})</option>`);
            });

            return options.join('');
        };

        const buildRow = (index) => {
            const row = document.createElement('tr');
            row.className = 'bookable-unit-item-row';
            row.innerHTML = `
                <td class="px-2 py-2">
                    <select name="items[${index}][bookable_unit_id]" class="bookable-unit-select w-full rounded-lg border-slate-300 text-xs" required>
                        ${buildUnitOptionsHtml()}
                    </select>
                    <input type="hidden" name="items[${index}][room_id]" class="room-id-hidden" value="">
                </td>
                <td class="px-2 py-2"><p class="included-rooms-text rounded-lg bg-slate-50 px-2 py-1 text-[11px] text-slate-600">-</p></td>
                <td class="px-2 py-2"><input type="number" name="items[${index}][nightly_rate]" min="0" step="0.01" class="nightly-rate-input w-full rounded-lg border-slate-300 text-xs" required></td>
                <td class="px-2 py-2"><input type="number" name="items[${index}][adults]" min="1" value="1" class="w-full rounded-lg border-slate-300 text-xs" required></td>
                <td class="px-2 py-2"><input type="number" name="items[${index}][children]" min="0" value="0" class="w-full rounded-lg border-slate-300 text-xs" required></td>
                <td class="px-2 py-2"><input type="date" name="items[${index}][check_in_date]" class="w-full rounded-lg border-slate-300 text-xs" required></td>
                <td class="px-2 py-2"><input type="date" name="items[${index}][check_out_date]" class="w-full rounded-lg border-slate-300 text-xs" required></td>
                <td class="px-2 py-2"><button type="button" class="remove-bookable-unit-item rounded-lg border border-rose-300 px-2 py-1 text-[11px] font-semibold text-rose-700 hover:bg-rose-50">Remove</button></td>
            `;

            return row;
        };

        const updateRowFromUnit = (row) => {
            const select = row.querySelector('.bookable-unit-select');
            const roomsLabel = row.querySelector('.included-rooms-text');
            const nightlyRateInput = row.querySelector('.nightly-rate-input');
            const roomIdHidden = row.querySelector('.room-id-hidden');

            if (!select || !roomsLabel || !nightlyRateInput || !roomIdHidden) {
                return;
            }

            const unit = unitMap[String(select.value)] || null;
            if (!unit) {
                roomsLabel.textContent = '-';
                roomIdHidden.value = '';
                return;
            }

            const rooms = Array.isArray(unit.rooms) ? unit.rooms : [];
            roomsLabel.textContent = rooms.length > 0
                ? rooms.map((room) => `${room.room_code} - ${room.room_name}`).join(', ')
                : '-';

            roomIdHidden.value = rooms.length > 0 ? String(rooms[0].room_id) : '';
            if (!nightlyRateInput.value) {
                nightlyRateInput.value = String(unit.base_nightly_rate ?? 0);
            }
        };

        addButton.addEventListener('click', function() {
            const index = tbody.querySelectorAll('tr.bookable-unit-item-row').length;
            tbody.appendChild(buildRow(index));
        });

        tbody.addEventListener('change', function(event) {
            const target = event.target;
            if (!(target instanceof HTMLElement) || !target.classList.contains('bookable-unit-select')) {
                return;
            }

            const row = target.closest('tr.bookable-unit-item-row');
            if (!row) {
                return;
            }

            updateRowFromUnit(row);
        });

        tbody.addEventListener('click', function(event) {
            const target = event.target;
            if (!(target instanceof HTMLElement) || !target.classList.contains('remove-bookable-unit-item')) {
                return;
            }

            const rows = tbody.querySelectorAll('tr.bookable-unit-item-row');
            if (rows.length <= 1) {
                return;
            }

            target.closest('tr.bookable-unit-item-row')?.remove();
        });

        tbody.querySelectorAll('tr.bookable-unit-item-row').forEach((row) => {
            updateRowFromUnit(row);
        });
    });
</script>
