<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingRoomItem;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;
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

            $this->assertRoomItemsAvailable($items, null);

            /** @var Booking $booking */
            $booking = Booking::query()->create($validated);

            $booking->bookingRoomItems()->createMany($items);

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

            $this->assertRoomItemsAvailable($items, $booking->id);

            $booking->update($validated);
            $booking->bookingRoomItems()->delete();
            $booking->bookingRoomItems()->createMany($items);
        });
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function assertRoomItemsAvailable(array $items, ?int $excludeBookingId): void
    {
        $errors = [];

        foreach ($items as $index => $item) {
            $roomId = (int) $item['room_id'];
            $checkIn = (string) $item['check_in_date'];
            $checkOut = (string) $item['check_out_date'];
            $totalGuests = (int) $item['adults'] + (int) $item['children'];

            // Serialize availability checks per room inside the surrounding transaction.
            $room = Room::query()->whereKey($roomId)->lockForUpdate()->first();
            if ($room === null) {
                $errors["items.$index.room_id"][] = 'Selected room does not exist.';
                continue;
            }

            if ($totalGuests > (int) $room->maximum_guests) {
                $errors["items.$index.adults"][] = 'Selected room does not have enough guest capacity.';
            }

            $overlapQuery = BookingRoomItem::query()
                ->where('room_id', $roomId)
                ->whereDate('check_in_date', '<', $checkOut)
                ->whereDate('check_out_date', '>', $checkIn)
                ->whereHas('booking', function ($query): void {
                    $query->whereIn('booking_status', Booking::BLOCKING_STATUSES);
                })
                ->lockForUpdate();

            if ($excludeBookingId !== null) {
                $overlapQuery->where('booking_id', '!=', $excludeBookingId);
            }

            if ($overlapQuery->exists()) {
                $errors["items.$index.room_id"][] = 'Selected room is not available for the chosen date range.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
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

        $unavailableRoomIdsQuery = BookingRoomItem::query()
            ->whereDate('check_in_date', '<', $checkOut)
            ->whereDate('check_out_date', '>', $checkIn)
            ->whereHas('booking', function ($query): void {
                $query->whereIn('booking_status', Booking::BLOCKING_STATUSES);
            });

        if ($excludeBookingId !== null) {
            $unavailableRoomIdsQuery->where('booking_id', '!=', $excludeBookingId);
        }

        $unavailableRoomIds = $unavailableRoomIdsQuery->pluck('room_id');

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
}
