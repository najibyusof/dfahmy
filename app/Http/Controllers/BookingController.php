<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Booking;
use App\Models\BookableUnit;
use App\Models\Building;
use App\Models\Guest;
use App\Models\Room;
use App\Services\BookingService;
use App\Services\GuestEmailNotificationService;
use App\Services\HousekeepingService;
use App\Services\InAppNotificationService;
use App\Services\TelegramAlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Booking::class);

        $filters = [
            'search' => (string) $request->string('search'),
            'booking_status' => (string) $request->string('booking_status'),
            'payment_summary' => (string) $request->string('payment_summary'),
            'quick' => (string) $request->string('quick'),
        ];

        $today = now()->toDateString();
        $paidTotalExpr = "COALESCE((SELECT SUM(CASE WHEN payments.payment_status = 'paid' THEN payments.amount ELSE 0 END) FROM payments WHERE payments.booking_id = bookings.id), 0)";

        $countsBaseQuery = Booking::query()
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $term = '%' . $filters['search'] . '%';

                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('booking_reference', 'like', $term)
                        ->orWhereHas('guest', function ($guestQuery) use ($term) {
                            $guestQuery->where('full_name', 'like', $term)
                                ->orWhere('email', 'like', $term)
                                ->orWhere('phone_number', 'like', $term)
                                ->orWhere('identification_number', 'like', $term);
                        })
                        ->orWhereHas('bookingRoomItems.room', function ($roomQuery) use ($term) {
                            $roomQuery->where('name', 'like', $term)
                                ->orWhere('code', 'like', $term);
                        });
                });
            })
            ->when($filters['booking_status'] !== '', function ($query) use ($filters) {
                $query->where('booking_status', $filters['booking_status']);
            })
            ->when($filters['payment_summary'] !== '', function ($query) use ($filters, $paidTotalExpr) {
                if ($filters['payment_summary'] === 'unpaid') {
                    $query->whereRaw('bookings.total_amount > 0')
                        ->whereRaw($paidTotalExpr . ' <= 0');
                } elseif ($filters['payment_summary'] === 'partially_paid') {
                    $query->whereRaw($paidTotalExpr . ' > 0')
                        ->whereRaw($paidTotalExpr . ' < bookings.total_amount');
                } elseif ($filters['payment_summary'] === 'paid') {
                    $query->whereRaw('ABS((' . $paidTotalExpr . ') - bookings.total_amount) <= 0.00001');
                } elseif ($filters['payment_summary'] === 'overpaid') {
                    $query->whereRaw($paidTotalExpr . ' > bookings.total_amount');
                }
            });

        $quickFilterCounts = [
            'all' => (clone $countsBaseQuery)->count(),
            'today' => (clone $countsBaseQuery)->whereDate('check_in_date', $today)->count(),
            'upcoming' => (clone $countsBaseQuery)->whereDate('check_in_date', '>', $today)->count(),
            'checked_in' => (clone $countsBaseQuery)->where('booking_status', 'checked_in')->count(),
            'checked_out' => (clone $countsBaseQuery)->where('booking_status', 'checked_out')->count(),
            'unpaid' => (clone $countsBaseQuery)
                ->whereRaw('bookings.total_amount > 0')
                ->whereRaw($paidTotalExpr . ' <= 0')
                ->count(),
            'partially_paid' => (clone $countsBaseQuery)
                ->whereRaw($paidTotalExpr . ' > 0')
                ->whereRaw($paidTotalExpr . ' < bookings.total_amount')
                ->count(),
        ];

        $bookings = Booking::query()
            ->with([
                'guest:id,full_name,email,phone_number',
                'bookingRoomItems.room:id,name,code',
                'payments:id,booking_id,amount,payment_status',
            ])
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $term = '%' . $filters['search'] . '%';

                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('booking_reference', 'like', $term)
                        ->orWhereHas('guest', function ($guestQuery) use ($term) {
                            $guestQuery->where('full_name', 'like', $term)
                                ->orWhere('email', 'like', $term)
                                ->orWhere('phone_number', 'like', $term)
                                ->orWhere('identification_number', 'like', $term);
                        })
                        ->orWhereHas('bookingRoomItems.room', function ($roomQuery) use ($term) {
                            $roomQuery->where('name', 'like', $term)
                                ->orWhere('code', 'like', $term);
                        });
                });
            })
            ->when($filters['booking_status'] !== '', function ($query) use ($filters) {
                $query->where('booking_status', $filters['booking_status']);
            })
            ->when($filters['payment_summary'] !== '', function ($query) use ($filters, $paidTotalExpr) {
                if ($filters['payment_summary'] === 'unpaid') {
                    $query->whereRaw('bookings.total_amount > 0')
                        ->whereRaw($paidTotalExpr . ' <= 0');
                } elseif ($filters['payment_summary'] === 'partially_paid') {
                    $query->whereRaw($paidTotalExpr . ' > 0')
                        ->whereRaw($paidTotalExpr . ' < bookings.total_amount');
                } elseif ($filters['payment_summary'] === 'paid') {
                    $query->whereRaw('ABS((' . $paidTotalExpr . ') - bookings.total_amount) <= 0.00001');
                } elseif ($filters['payment_summary'] === 'overpaid') {
                    $query->whereRaw($paidTotalExpr . ' > bookings.total_amount');
                }
            })
            ->when($filters['quick'] === 'today', function ($query) use ($today) {
                $query->whereDate('check_in_date', $today);
            })
            ->when($filters['quick'] === 'upcoming', function ($query) use ($today) {
                $query->whereDate('check_in_date', '>', $today);
            })
            ->when($filters['quick'] === 'checked_in', function ($query) {
                $query->where('booking_status', 'checked_in');
            })
            ->when($filters['quick'] === 'checked_out', function ($query) {
                $query->where('booking_status', 'checked_out');
            })
            ->when($filters['quick'] === 'unpaid', function ($query) use ($paidTotalExpr) {
                $query->whereRaw('bookings.total_amount > 0')
                    ->whereRaw($paidTotalExpr . ' <= 0');
            })
            ->when($filters['quick'] === 'partially_paid', function ($query) use ($paidTotalExpr) {
                $query->whereRaw($paidTotalExpr . ' > 0')
                    ->whereRaw($paidTotalExpr . ' < bookings.total_amount');
            })
            ->orderByDesc('check_in_date')
            ->paginate(10)
            ->withQueryString();

        $statusBadgeClasses = [
            'inquiry' => 'bg-blue-100 text-blue-800',
            'pending' => 'bg-amber-100 text-amber-800',
            'confirmed' => 'bg-emerald-100 text-emerald-800',
            'checked_in' => 'bg-emerald-100 text-emerald-800',
            'checked_out' => 'bg-slate-200 text-slate-800',
            'cancelled' => 'bg-rose-100 text-rose-800',
            'no_show' => 'bg-rose-200 text-rose-900',
        ];

        return view('bookings.index', [
            'bookings' => $bookings,
            'filters' => $filters,
            'statuses' => Booking::STATUSES,
            'statusBadgeClasses' => $statusBadgeClasses,
            'quickFilterCounts' => $quickFilterCounts,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Booking::class);

        $selectedGuestId = $request->integer('guest');
        $availabilityFilters = $this->availabilityFilters($request);

        $bookingService = app(BookingService::class);
        $availableBookableUnits = $bookingService->searchAvailableBookableUnits($availabilityFilters);
        $availableRooms = $bookingService->searchAvailableRooms($availabilityFilters);

        return view('bookings.create', [
            'guests' => Guest::query()->orderBy('full_name')->get(['id', 'full_name', 'email', 'phone_number']),
            'rooms' => $availableRooms,
            'allRooms' => Room::query()->where('is_active', true)->orderBy('code')->get(['id', 'name', 'code']),
            'bookableUnits' => $availableBookableUnits,
            'allBookableUnits' => BookableUnit::query()
                ->with(['rooms:id,name,code'])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'booking_type', 'base_nightly_rate', 'maximum_guests']),
            'buildings' => Building::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => Booking::STATUSES,
            'sources' => Booking::SOURCES,
            'selectedGuestId' => $selectedGuestId > 0 ? $selectedGuestId : null,
            'availabilityFilters' => $availabilityFilters,
            'availabilitySearched' => $availabilityFilters['check_in_date'] !== '' && $availabilityFilters['check_out_date'] !== '',
        ]);
    }

    public function store(
        StoreBookingRequest $request,
        BookingService $bookingService,
        InAppNotificationService $notificationService,
        GuestEmailNotificationService $guestEmailNotificationService,
        TelegramAlertService $telegramAlertService
    ): RedirectResponse {
        $this->authorize('create', Booking::class);

        $booking = $bookingService->createWithItems($request->validated());

        $notificationService->notifyNewBooking($booking);
        $telegramAlertService->newBooking($booking->loadMissing('guest'));
        if ($booking->booking_status === 'confirmed') {
            $notificationService->notifyBookingConfirmed($booking);
            $guestEmailNotificationService->sendBookingConfirmation($booking);
        }

        return redirect()->route('bookings.index')->with('status', 'booking-created');
    }

    public function show(Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load(['guest', 'bookingRoomItems.room', 'bookingRoomItems.includedRooms', 'payments.receivedBy']);

        return view('bookings.show', [
            'booking' => $booking,
        ]);
    }

    public function edit(Request $request, Booking $booking): View
    {
        $this->authorize('update', $booking);

        $availabilityFilters = $this->availabilityFilters($request, $booking);
        $bookingService = app(BookingService::class);
        $availableBookableUnits = $bookingService->searchAvailableBookableUnits($availabilityFilters, $booking->id);
        $availableRooms = $bookingService->searchAvailableRooms($availabilityFilters, $booking->id);

        return view('bookings.edit', [
            'booking' => $booking,
            'guests' => Guest::query()->orderBy('full_name')->get(['id', 'full_name', 'email', 'phone_number']),
            'rooms' => $availableRooms,
            'allRooms' => Room::query()->where('is_active', true)->orderBy('code')->get(['id', 'name', 'code']),
            'bookableUnits' => $availableBookableUnits,
            'allBookableUnits' => BookableUnit::query()
                ->with(['rooms:id,name,code'])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'booking_type', 'base_nightly_rate', 'maximum_guests']),
            'buildings' => Building::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => Booking::STATUSES,
            'sources' => Booking::SOURCES,
            'availabilityFilters' => $availabilityFilters,
            'availabilitySearched' => $availabilityFilters['check_in_date'] !== '' && $availabilityFilters['check_out_date'] !== '',
        ]);
    }

    public function update(
        UpdateBookingRequest $request,
        Booking $booking,
        BookingService $bookingService,
        InAppNotificationService $notificationService,
        GuestEmailNotificationService $guestEmailNotificationService
    ): RedirectResponse {
        $this->authorize('update', $booking);

        $previousStatus = $booking->booking_status;

        $bookingService->updateWithItems($booking, $request->validated());
        $booking->refresh();

        if ($previousStatus !== 'confirmed' && $booking->booking_status === 'confirmed') {
            $notificationService->notifyBookingConfirmed($booking);
            $guestEmailNotificationService->sendBookingConfirmation($booking);
        }

        return redirect()->route('bookings.index')->with('status', 'booking-updated');
    }

    public function cancelPage(Booking $booking): View
    {
        $this->authorize('update', $booking);

        return view('bookings.cancel', ['booking' => $booking]);
    }

    public function cancel(
        Booking $booking,
        InAppNotificationService $notificationService,
        GuestEmailNotificationService $guestEmailNotificationService,
        TelegramAlertService $telegramAlertService
    ): RedirectResponse {
        $this->authorize('update', $booking);

        if (! in_array($booking->booking_status, ['checked_out', 'cancelled', 'no_show'], true)) {
            $booking->update(['booking_status' => 'cancelled']);
            $notificationService->notifyBookingCancelled($booking);
            $guestEmailNotificationService->sendBookingCancellation($booking);
            $telegramAlertService->bookingCancellation($booking->loadMissing('guest'));
        }

        return redirect()->route('bookings.show', $booking)->with('status', 'booking-cancelled');
    }

    public function checkInPage(Booking $booking): View
    {
        $this->authorize('update', $booking);

        return view('bookings.check-in', ['booking' => $booking]);
    }

    public function checkIn(
        Booking $booking,
        InAppNotificationService $notificationService,
        GuestEmailNotificationService $guestEmailNotificationService,
        TelegramAlertService $telegramAlertService
    ): RedirectResponse {
        $this->authorize('update', $booking);

        if (in_array($booking->booking_status, ['confirmed', 'pending', 'inquiry'], true)) {
            $outstandingBalance = $booking->outstandingBalanceAmount();
            if ($outstandingBalance > 0.0) {
                $notificationService->notifyOutstandingBalanceBeforeCheckIn($booking, $outstandingBalance);
                $guestEmailNotificationService->sendOutstandingBalanceReminder($booking, $outstandingBalance);
                $telegramAlertService->overduePaymentOrOutstandingBalance($booking->loadMissing('guest'), $outstandingBalance);
            }

            $booking->update(['booking_status' => 'checked_in']);
            $notificationService->notifyCheckIn($booking);
            $telegramAlertService->checkIn($booking->loadMissing('guest'));
        }

        return redirect()->route('bookings.show', $booking)->with('status', 'booking-checked-in');
    }

    public function checkOutPage(Booking $booking): View
    {
        $this->authorize('update', $booking);

        return view('bookings.check-out', ['booking' => $booking]);
    }

    public function checkOut(
        Booking $booking,
        HousekeepingService $housekeepingService,
        InAppNotificationService $notificationService,
        TelegramAlertService $telegramAlertService
    ): RedirectResponse {
        $this->authorize('update', $booking);

        if ($booking->booking_status === 'checked_in') {
            $booking->update(['booking_status' => 'checked_out']);
            $housekeepingService->createCheckoutTasksForBooking($booking, request()->user());
            $notificationService->notifyCheckOut($booking);
            $telegramAlertService->checkOut($booking->loadMissing('guest'));
        }

        return redirect()->route('bookings.show', $booking)->with('status', 'booking-checked-out');
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $this->authorize('delete', $booking);

        $booking->delete();

        return redirect()->route('bookings.index')->with('status', 'booking-deleted');
    }

    /**
     * @return array<string, string>
     */
    private function availabilityFilters(Request $request, ?Booking $booking = null): array
    {
        return [
            'check_in_date' => (string) $request->string('availability_check_in', $booking?->check_in_date?->format('Y-m-d') ?? ''),
            'check_out_date' => (string) $request->string('availability_check_out', $booking?->check_out_date?->format('Y-m-d') ?? ''),
            'adults' => (string) $request->string('availability_adults', (string) ($booking?->adults ?? 1)),
            'children' => (string) $request->string('availability_children', (string) ($booking?->children ?? 0)),
            'building_id' => (string) $request->string('availability_building_id'),
            'room_id' => (string) $request->string('availability_room_id'),
        ];
    }
}
