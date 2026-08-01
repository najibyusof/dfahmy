<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookableUnit;
use App\Models\BookingRoomItem;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function createWithItems(array $validated): Booking
    {
        return DB::transaction(function () use ($validated): Booking {
            $items = $validated['items'];
            unset($validated['items']);

            $resolved = $this->resolveAndValidateItems($items, null);

            /** @var Booking $booking */
            $booking = Booking::query()->create($validated);

            $this->persistBookingItems($booking, $resolved);

            return $booking;
        });
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function updateWithItems(Booking $booking, array $validated): void
    {
        DB::transaction(function () use ($booking, $validated): void {
            $items = $validated['items'];
            unset($validated['items']);

            $resolved = $this->resolveAndValidateItems($items, $booking->id);

            $booking->update($validated);
            $booking->bookingRoomItems()->delete();
            $this->persistBookingItems($booking, $resolved);
        });
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function assertRoomItemsAvailable(array $items, ?int $excludeBookingId): void
    {
        $this->resolveAndValidateItems($items, $excludeBookingId);
    }

    /**
     * @param array<string, mixed> $filters
     * @return Collection<int, Room>
     */
    public function searchAvailableRooms(array $filters, ?int $excludeBookingId = null): Collection
    {
        $checkIn = (string) ($filters['check_in_date'] ?? '');
        $checkOut = (string) ($filters['check_out_date'] ?? '');

        if ($checkIn === '' || $checkOut === '') {
            return new Collection();
        }

        $requiredGuests = max(1, (int) ($filters['adults'] ?? 1)) + max(0, (int) ($filters['children'] ?? 0));
        $unavailableRoomIds = $this->getUnavailableRoomIds($checkIn, $checkOut, $excludeBookingId);

        return Room::query()
            ->with('building:id,name')
            ->where('is_active', true)
            ->when(($filters['building_id'] ?? '') !== '', function ($query) use ($filters): void {
                $query->where('building_id', (int) $filters['building_id']);
            })
            ->when(($filters['room_id'] ?? '') !== '', function ($query) use ($filters): void {
                $query->where('id', (int) $filters['room_id']);
            })
            ->where('maximum_guests', '>=', $requiredGuests)
            ->whereNotIn('id', $unavailableRoomIds)
            ->orderBy('code')
            ->get();
    }

    /**
     * @param array<string, mixed> $filters
     * @return Collection<int, BookableUnit>
     */
    public function searchAvailableBookableUnits(array $filters, ?int $excludeBookingId = null): Collection
    {
        $checkIn = (string) ($filters['check_in_date'] ?? '');
        $checkOut = (string) ($filters['check_out_date'] ?? '');

        if ($checkIn === '' || $checkOut === '') {
            return new Collection();
        }

        $requiredGuests = max(1, (int) ($filters['adults'] ?? 1)) + max(0, (int) ($filters['children'] ?? 0));
        $unavailableRoomIds = $this->getUnavailableRoomIds($checkIn, $checkOut, $excludeBookingId);

        /** @var Collection<int, BookableUnit> $units */
        $units = BookableUnit::query()
            ->with(['rooms:id,building_id,name,code,maximum_guests', 'rooms.building:id,name'])
            ->where('is_active', true)
            ->where('maximum_guests', '>=', $requiredGuests)
            ->when(($filters['building_id'] ?? '') !== '', function ($query) use ($filters): void {
                $query->whereHas('rooms', function ($roomQuery) use ($filters): void {
                    $roomQuery->where('building_id', (int) $filters['building_id']);
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $units->filter(function (BookableUnit $unit) use ($unavailableRoomIds): bool {
            $unitRoomIds = $unit->rooms->pluck('id')->all();
            if ($unitRoomIds === []) {
                return false;
            }

            return count(array_intersect($unitRoomIds, $unavailableRoomIds)) === 0;
        })->values();
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array{attributes: array<string,mixed>, room_ids: array<int>}> 
     */
    private function resolveAndValidateItems(array $items, ?int $excludeBookingId): array
    {
        $errors = [];
        $resolved = [];

        foreach ($items as $index => $item) {
            $resolvedItem = $this->resolveOneItem($item, $index, $errors);
            if ($resolvedItem === null) {
                continue;
            }

            $resolved[$index] = $resolvedItem;
        }

        $this->assertNoOverlapWithinRequest($resolved, $errors);
        $this->assertNoOverlapAgainstExistingBookings($resolved, $excludeBookingId, $errors);

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return array_values($resolved);
    }

    /**
     * @param array<string, mixed> $item
     * @param array<string, array<int, string>> $errors
     * @return array{attributes: array<string,mixed>, room_ids: array<int>}|null
     */
    private function resolveOneItem(array $item, int $index, array &$errors): ?array
    {
        $checkIn = (string) ($item['check_in_date'] ?? '');
        $checkOut = (string) ($item['check_out_date'] ?? '');
        $adults = max(1, (int) ($item['adults'] ?? 1));
        $children = max(0, (int) ($item['children'] ?? 0));
        $totalGuests = $adults + $children;
        $bookableUnitId = Arr::has($item, 'bookable_unit_id') && (string) $item['bookable_unit_id'] !== ''
            ? (int) $item['bookable_unit_id']
            : null;

        if ($bookableUnitId !== null) {
            $unit = BookableUnit::query()->with(['rooms:id,name,code,maximum_guests'])->find($bookableUnitId);
            if ($unit === null || ! $unit->is_active) {
                $errors["items.$index.bookable_unit_id"][] = 'Selected bookable unit is invalid or inactive.';
                return null;
            }

            $roomIds = $unit->rooms->pluck('id')->all();
            if ($roomIds === []) {
                $errors["items.$index.bookable_unit_id"][] = 'Selected bookable unit has no assigned rooms.';
                return null;
            }

            if ($totalGuests > (int) $unit->maximum_guests) {
                $errors["items.$index.adults"][] = 'Selected bookable unit does not have enough guest capacity.';
            }

            $snapshotRooms = $unit->rooms
                ->map(static fn(Room $room): array => [
                    'room_id' => $room->id,
                    'room_code' => $room->code,
                    'room_name' => $room->name,
                ])
                ->values()
                ->all();

            $nightlyRate = (float) ($item['nightly_rate'] ?? $unit->base_nightly_rate);

            return [
                'attributes' => [
                    'bookable_unit_id' => $unit->id,
                    'bookable_unit_name' => $unit->name,
                    'bookable_unit_code' => $unit->code,
                    'booking_type' => $unit->booking_type,
                    'included_rooms_snapshot' => $snapshotRooms,
                    'room_id' => (int) $roomIds[0],
                    'nightly_rate' => $nightlyRate,
                    'adults' => $adults,
                    'children' => $children,
                    'check_in_date' => $checkIn,
                    'check_out_date' => $checkOut,
                ],
                'room_ids' => array_map('intval', $roomIds),
            ];
        }

        $roomId = (int) ($item['room_id'] ?? 0);
        $room = Room::query()->whereKey($roomId)->first();
        if ($room === null) {
            $errors["items.$index.room_id"][] = 'Selected room does not exist.';
            return null;
        }

        if ($totalGuests > (int) $room->maximum_guests) {
            $errors["items.$index.adults"][] = 'Selected room does not have enough guest capacity.';
        }

        $nightlyRate = (float) ($item['nightly_rate'] ?? $room->base_nightly_rate);

        return [
            'attributes' => [
                'bookable_unit_id' => null,
                'bookable_unit_name' => 'Room: ' . $room->code . ' - ' . $room->name,
                'bookable_unit_code' => 'ROOM-' . $room->code,
                'booking_type' => 'room',
                'included_rooms_snapshot' => [[
                    'room_id' => $room->id,
                    'room_code' => $room->code,
                    'room_name' => $room->name,
                ]],
                'room_id' => $room->id,
                'nightly_rate' => $nightlyRate,
                'adults' => $adults,
                'children' => $children,
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
            ],
            'room_ids' => [$room->id],
        ];
    }

    /**
     * @param array<int, array{attributes: array<string,mixed>, room_ids: array<int>}> $resolved
     * @param array<string, array<int, string>> $errors
     */
    private function assertNoOverlapWithinRequest(array $resolved, array &$errors): void
    {
        $count = count($resolved);

        for ($left = 0; $left < $count; $left++) {
            for ($right = $left + 1; $right < $count; $right++) {
                $leftItem = $resolved[$left];
                $rightItem = $resolved[$right];

                $datesOverlap = (string) $leftItem['attributes']['check_in_date'] < (string) $rightItem['attributes']['check_out_date']
                    && (string) $leftItem['attributes']['check_out_date'] > (string) $rightItem['attributes']['check_in_date'];

                if (! $datesOverlap) {
                    continue;
                }

                if (count(array_intersect($leftItem['room_ids'], $rightItem['room_ids'])) > 0) {
                    $errors['items'][] = 'Selected booking units overlap in room inventory for overlapping dates.';
                    return;
                }
            }
        }
    }

    /**
     * @param array<int, array{attributes: array<string,mixed>, room_ids: array<int>}> $resolved
     * @param array<string, array<int, string>> $errors
     */
    private function assertNoOverlapAgainstExistingBookings(array $resolved, ?int $excludeBookingId, array &$errors): void
    {
        foreach ($resolved as $index => $resolvedItem) {
            $roomIds = $resolvedItem['room_ids'];
            if ($roomIds === []) {
                continue;
            }

            Room::query()->whereIn('id', $roomIds)->lockForUpdate()->get(['id']);

            $checkIn = (string) $resolvedItem['attributes']['check_in_date'];
            $checkOut = (string) $resolvedItem['attributes']['check_out_date'];

            $overlapQuery = BookingRoomItem::query()
                ->whereDate('check_in_date', '<', $checkOut)
                ->whereDate('check_out_date', '>', $checkIn)
                ->whereHas('booking', function ($query): void {
                    $query->whereIn('booking_status', Booking::BLOCKING_STATUSES);
                })
                ->where(function ($query) use ($roomIds): void {
                    $query->whereIn('room_id', $roomIds)
                        ->orWhereExists(function ($existsQuery) use ($roomIds): void {
                            $existsQuery->select(DB::raw(1))
                                ->from('booking_room_item_rooms as brir')
                                ->whereColumn('brir.booking_room_item_id', 'booking_room_items.id')
                                ->whereIn('brir.room_id', $roomIds);
                        });
                })
                ->lockForUpdate();

            if ($excludeBookingId !== null) {
                $overlapQuery->where('booking_id', '!=', $excludeBookingId);
            }

            if ($overlapQuery->exists()) {
                $hasBookableUnit = Arr::get($resolvedItem, 'attributes.bookable_unit_id') !== null;
                $errorKey = $hasBookableUnit ? "items.$index.bookable_unit_id" : "items.$index.room_id";
                $errors[$errorKey][] = 'Selected unit is not available for the chosen date range.';
            }
        }
    }

    /**
     * @param array<int, array{attributes: array<string,mixed>, room_ids: array<int>}> $resolved
     */
    private function persistBookingItems(Booking $booking, array $resolved): void
    {
        foreach ($resolved as $resolvedItem) {
            /** @var BookingRoomItem $bookingItem */
            $bookingItem = $booking->bookingRoomItems()->create($resolvedItem['attributes']);
            $rows = array_map(static function (int $roomId) use ($bookingItem): array {
                return [
                    'booking_room_item_id' => $bookingItem->id,
                    'room_id' => $roomId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, array_values(array_unique($resolvedItem['room_ids'])));

            if ($rows !== []) {
                DB::table('booking_room_item_rooms')->insert($rows);
            }
        }
    }

    /**
     * @return array<int>
     */
    private function getUnavailableRoomIds(string $checkIn, string $checkOut, ?int $excludeBookingId = null): array
    {
        $query = DB::table('booking_room_items as bri')
            ->join('bookings as b', 'b.id', '=', 'bri.booking_id')
            ->leftJoin('booking_room_item_rooms as brir', 'brir.booking_room_item_id', '=', 'bri.id')
            ->whereDate('bri.check_in_date', '<', $checkOut)
            ->whereDate('bri.check_out_date', '>', $checkIn)
            ->whereIn('b.booking_status', Booking::BLOCKING_STATUSES)
            ->when($excludeBookingId !== null, function ($builder) use ($excludeBookingId): void {
                $builder->where('bri.booking_id', '!=', $excludeBookingId);
            })
            ->where(function ($builder): void {
                $builder->whereNotNull('brir.room_id')
                    ->orWhereNotNull('bri.room_id');
            })
            ->selectRaw('DISTINCT COALESCE(brir.room_id, bri.room_id) as room_id');

        /** @var array<int> $roomIds */
        $roomIds = $query->pluck('room_id')->map(static fn($id): int => (int) $id)->all();

        return $roomIds;
    }
}
