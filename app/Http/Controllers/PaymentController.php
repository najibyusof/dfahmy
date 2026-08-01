<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Services\GuestEmailNotificationService;
use App\Services\InAppNotificationService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Payment::class);

        $filters = [
            'search' => (string) $request->string('search'),
            'payment_status' => (string) $request->string('payment_status'),
            'payment_method' => (string) $request->string('payment_method'),
            'booking_id' => (string) $request->string('booking_id'),
        ];

        $payments = Payment::query()
            ->with(['booking:id,booking_reference,guest_id,total_amount', 'booking.guest:id,full_name', 'receivedBy:id,name'])
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $term = '%' . $filters['search'] . '%';
                $query->where(function ($subQuery) use ($term): void {
                    $subQuery->where('receipt_number', 'like', $term)
                        ->orWhere('reference_number', 'like', $term)
                        ->orWhereHas('booking', function ($bookingQuery) use ($term): void {
                            $bookingQuery->where('booking_reference', 'like', $term)
                                ->orWhereHas('guest', function ($guestQuery) use ($term): void {
                                    $guestQuery->where('full_name', 'like', $term);
                                });
                        });
                });
            })
            ->when($filters['payment_status'] !== '', function ($query) use ($filters): void {
                $query->where('payment_status', $filters['payment_status']);
            })
            ->when($filters['payment_method'] !== '', function ($query) use ($filters): void {
                $query->where('payment_method', $filters['payment_method']);
            })
            ->when($filters['booking_id'] !== '', function ($query) use ($filters): void {
                $query->where('booking_id', (int) $filters['booking_id']);
            })
            ->orderByDesc('payment_date')
            ->paginate(12)
            ->withQueryString();

        $bookings = Booking::query()->orderByDesc('id')->limit(100)->get(['id', 'booking_reference']);

        return view('payments.index', [
            'payments' => $payments,
            'filters' => $filters,
            'paymentStatuses' => Payment::STATUSES,
            'paymentMethods' => Payment::METHODS,
            'bookings' => $bookings,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Payment::class);

        $selectedBookingId = $request->integer('booking');

        return view('payments.create', [
            'payment' => null,
            'selectedBookingId' => $selectedBookingId > 0 ? $selectedBookingId : null,
            'bookings' => Booking::query()->with('guest:id,full_name')->orderByDesc('id')->get(['id', 'booking_reference', 'guest_id', 'total_amount']),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'paymentStatuses' => Payment::STATUSES,
            'paymentMethods' => Payment::METHODS,
            'suggestedReceiptNumber' => 'RCPT-' . now()->format('Ymd-His'),
        ]);
    }

    public function store(
        StorePaymentRequest $request,
        PaymentService $paymentService,
        InAppNotificationService $notificationService,
        GuestEmailNotificationService $guestEmailNotificationService
    ): RedirectResponse {
        $this->authorize('create', Payment::class);

        $payment = $paymentService->create($request->validated(), $request->user());
        $notificationService->notifyNewPayment($payment);
        $guestEmailNotificationService->sendPaymentReceipt($payment);

        return redirect()->route('payments.show', $payment)->with('status', 'payment-created');
    }

    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);

        $payment->load(['booking.guest', 'receivedBy']);
        $booking = $payment->booking->load('payments');

        return view('payments.show', [
            'payment' => $payment,
            'booking' => $booking,
        ]);
    }

    public function edit(Payment $payment): View
    {
        $this->authorize('update', $payment);

        return view('payments.edit', [
            'payment' => $payment,
            'bookings' => Booking::query()->with('guest:id,full_name')->orderByDesc('id')->get(['id', 'booking_reference', 'guest_id', 'total_amount']),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'paymentStatuses' => Payment::STATUSES,
            'paymentMethods' => Payment::METHODS,
            'selectedBookingId' => $payment->booking_id,
            'suggestedReceiptNumber' => null,
        ]);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment, PaymentService $paymentService): RedirectResponse
    {
        $this->authorize('update', $payment);

        $paymentService->update($payment, $request->validated(), $request->user());

        return redirect()->route('payments.show', $payment)->with('status', 'payment-updated');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $this->authorize('delete', $payment);

        $payment->delete();

        return redirect()->route('payments.index')->with('status', 'payment-deleted');
    }

    public function refundPage(Payment $payment): View
    {
        $this->authorize('update', $payment);

        return view('payments.refund', ['payment' => $payment->load('booking.guest')]);
    }

    public function refund(Request $request, Payment $payment, PaymentService $paymentService): RedirectResponse
    {
        $this->authorize('update', $payment);

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $paymentService->refund($payment, $validated['notes'] ?? null);

        return redirect()->route('payments.show', $payment)->with('status', 'payment-refunded');
    }

    public function voidPage(Payment $payment): View
    {
        $this->authorize('update', $payment);

        return view('payments.void', ['payment' => $payment->load('booking.guest')]);
    }

    public function void(Request $request, Payment $payment, PaymentService $paymentService): RedirectResponse
    {
        $this->authorize('update', $payment);

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $paymentService->void($payment, $validated['notes'] ?? null);

        return redirect()->route('payments.show', $payment)->with('status', 'payment-voided');
    }

    public function invoice(Booking $booking): View
    {
        $this->authorize('viewAny', Payment::class);

        $booking->load(['guest', 'bookingRoomItems.room', 'payments.receivedBy']);

        return view('payments.invoice', [
            'booking' => $booking,
            'totalPaid' => $booking->totalPaidAmount(),
            'outstanding' => $booking->outstandingBalanceAmount(),
            'paymentSummaryStatus' => $booking->paymentSummaryStatus(),
        ]);
    }

    public function receipt(Payment $payment): View
    {
        $this->authorize('view', $payment);

        $payment->load(['booking.guest', 'booking.bookingRoomItems.room', 'receivedBy']);

        return view('payments.receipt', [
            'payment' => $payment,
            'booking' => $payment->booking,
        ]);
    }
}
