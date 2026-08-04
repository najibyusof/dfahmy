<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Services\BookingService;
use App\Services\InAppNotificationService;
use App\Services\TelegramAlertService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GuestPortalController extends Controller
{
    public function index(Request $request, BookingService $bookingService): View
    {
        $unlinkedGuest = $this->unlinkedGuestFor($request);
        if ($unlinkedGuest !== null) {
            return view('guest-portal.link-history', ['email' => $request->user()->email]);
        }

        $guest = $this->guestFor($request);
        $filters = Validator::make($request->query(), [
            'check_in_date' => ['nullable', 'date', 'after_or_equal:today'],
            'check_out_date' => ['nullable', 'date', 'after:check_in_date'],
            'adults' => ['nullable', 'integer', 'min:1', 'max:50'],
            'children' => ['nullable', 'integer', 'min:0', 'max:50'],
        ])->validate();

        $filters = [
            'check_in_date' => (string) ($filters['check_in_date'] ?? now()->toDateString()),
            'check_out_date' => (string) ($filters['check_out_date'] ?? now()->addDay()->toDateString()),
            'adults' => (string) ($filters['adults'] ?? 1),
            'children' => (string) ($filters['children'] ?? 0),
        ];

        $availabilityDays = collect(range(0, 13))->map(function (int $offset) use ($bookingService): array {
            $date = now()->addDays($offset)->startOfDay();
            $availableCount = $bookingService->searchAvailableRooms([
                'check_in_date' => $date->toDateString(),
                'check_out_date' => $date->copy()->addDay()->toDateString(),
                'adults' => 1,
                'children' => 0,
            ])->count();

            return ['date' => $date, 'available_count' => $availableCount];
        });

        $bookings = $guest->bookings()
            ->with('bookingRoomItems.room:id,name,code')
            ->orderByDesc('check_in_date')
            ->get();

        return view('guest-portal.index', [
            'guest' => $guest,
            'filters' => $filters,
            'availabilityDays' => $availabilityDays,
            'availableRooms' => $bookingService->searchAvailableRooms($filters),
            'bookings' => $bookings,
        ]);
    }

    public function store(
        Request $request,
        BookingService $bookingService,
        InAppNotificationService $notificationService,
        TelegramAlertService $telegramAlertService
    ): RedirectResponse {
        $guest = $this->guestFor($request);
        $validated = $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'adults' => ['required', 'integer', 'min:1', 'max:50'],
            'children' => ['required', 'integer', 'min:0', 'max:50'],
            'phone_number' => ['required', 'string', 'max:30', Rule::unique('guests')->ignore($guest->id)],
            'identification_number' => ['required', 'string', 'max:100', Rule::unique('guests')->ignore($guest->id)],
            'special_requests' => ['nullable', 'string', 'max:2000'],
        ]);

        $room = Room::query()->where('is_active', true)->findOrFail((int) $validated['room_id']);
        $availabilityFilters = [
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'adults' => $validated['adults'],
            'children' => $validated['children'],
            'room_id' => $room->id,
        ];

        if (! $bookingService->searchAvailableRooms($availabilityFilters)->contains('id', $room->id)) {
            throw ValidationException::withMessages(['room_id' => 'This room is no longer available for the selected dates.']);
        }

        $guest->update([
            'full_name' => $request->user()->name,
            'email' => $request->user()->email,
            'phone_number' => $validated['phone_number'],
            'identification_number' => $validated['identification_number'],
        ]);

        $nights = Carbon::parse($validated['check_in_date'])->diffInDays(Carbon::parse($validated['check_out_date']));
        $subtotal = round((float) $room->base_nightly_rate * $nights, 2);
        $booking = $bookingService->createWithItems([
            'guest_id' => $guest->id,
            'booking_reference' => $this->bookingReference(),
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'adults' => $validated['adults'],
            'children' => $validated['children'],
            'booking_source' => 'website',
            'booking_status' => 'pending',
            'special_requests' => $validated['special_requests'] ?? null,
            'internal_notes' => null,
            'subtotal' => $subtotal,
            'discount' => 0,
            'tax' => 0,
            'total_amount' => $subtotal,
            'items' => [[
                'room_id' => $room->id,
                'nightly_rate' => $room->base_nightly_rate,
                'adults' => $validated['adults'],
                'children' => $validated['children'],
                'check_in_date' => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
            ]],
        ]);

        $notificationService->notifyNewBooking($booking);
        $telegramAlertService->newBooking($booking->loadMissing('guest'));

        return redirect()->route('guest.portal')->with('guest_booking_status', 'booking-requested');
    }

    public function linkHistory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'max:30'],
            'identification_number' => ['required', 'string', 'max:100'],
        ]);
        $guest = $this->unlinkedGuestFor($request);

        if ($guest === null
            || ! hash_equals((string) $guest->phone_number, $validated['phone_number'])
            || ! hash_equals((string) $guest->identification_number, $validated['identification_number'])) {
            throw ValidationException::withMessages([
                'identification_number' => 'The details do not match your existing guest record.',
            ]);
        }

        $guest->update(['user_id' => $request->user()->id]);

        return redirect()->route('guest.portal')->with('status', 'booking-history-linked');
    }

    private function guestFor(Request $request): Guest
    {
        $user = $request->user();
        $guest = $user->guest()->first();
        if ($guest !== null) {
            return $guest;
        }

        $guest = Guest::query()->where('email', $user->email)->first();
        if ($guest !== null) {
            abort(409, 'This email is already linked to another guest account.');
        }

        return Guest::query()->create([
            'user_id' => $user->id,
            'full_name' => $user->name,
            'email' => $user->email,
        ]);
    }

    private function unlinkedGuestFor(Request $request): ?Guest
    {
        if ($request->user()->guest()->exists()) {
            return null;
        }

        return Guest::query()
            ->where('email', $request->user()->email)
            ->whereNull('user_id')
            ->first();
    }

    private function bookingReference(): string
    {
        do {
            $reference = 'WEB-' . now()->format('ymd') . '-' . Str::upper(Str::random(6));
        } while (Booking::query()->where('booking_reference', $reference)->exists());

        return $reference;
    }
}
