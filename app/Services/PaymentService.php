<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function create(array $validated, User $actor): Payment
    {
        return DB::transaction(function () use ($validated, $actor): Payment {
            /** @var Booking $booking */
            $booking = Booking::query()->whereKey((int) $validated['booking_id'])->lockForUpdate()->firstOrFail();

            $this->assertNoOverpayment(
                $booking,
                (float) $validated['amount'],
                (string) $validated['payment_status'] === 'paid',
                $actor->can('payments.overpay.override')
            );

            /** @var Payment $payment */
            $payment = Payment::query()->create($validated);

            return $payment;
        });
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Payment $payment, array $validated, User $actor): void
    {
        DB::transaction(function () use ($payment, $validated, $actor): void {
            /** @var Booking $booking */
            $booking = Booking::query()->whereKey((int) $validated['booking_id'])->lockForUpdate()->firstOrFail();

            $this->assertNoOverpayment(
                $booking,
                (float) $validated['amount'],
                (string) $validated['payment_status'] === 'paid',
                $actor->can('payments.overpay.override'),
                $payment
            );

            $payment->update($validated);
        });
    }

    public function refund(Payment $payment, ?string $notes = null): void
    {
        DB::transaction(function () use ($payment, $notes): void {
            if ($payment->payment_status === 'voided') {
                throw ValidationException::withMessages([
                    'payment_status' => 'Voided payment cannot be refunded.',
                ]);
            }

            $payment->update([
                'payment_status' => 'refunded',
                'notes' => $this->appendOperationalNote($payment->notes, 'Refund recorded', $notes),
            ]);
        });
    }

    public function void(Payment $payment, ?string $notes = null): void
    {
        DB::transaction(function () use ($payment, $notes): void {
            if ($payment->payment_status === 'refunded') {
                throw ValidationException::withMessages([
                    'payment_status' => 'Refunded payment cannot be voided.',
                ]);
            }

            $payment->update([
                'payment_status' => 'voided',
                'notes' => $this->appendOperationalNote($payment->notes, 'Payment voided', $notes),
            ]);
        });
    }

    private function assertNoOverpayment(
        Booking $booking,
        float $incomingAmount,
        bool $isPaid,
        bool $canOverride,
        ?Payment $existingPayment = null
    ): void {
        if (! $isPaid || $canOverride) {
            return;
        }

        $paidQuery = $booking->payments()->where('payment_status', 'paid')->lockForUpdate();
        if ($existingPayment !== null) {
            $paidQuery->where('id', '!=', $existingPayment->id);
        }

        $existingPaid = (float) $paidQuery->sum('amount');
        $projectedPaid = $existingPaid + $incomingAmount;

        if ($projectedPaid - (float) $booking->total_amount > 0.00001) {
            throw ValidationException::withMessages([
                'amount' => 'Payment amount exceeds outstanding balance. Use overpayment override permission to proceed.',
            ]);
        }
    }

    private function appendOperationalNote(?string $originalNotes, string $operation, ?string $newNote): string
    {
        $parts = [];

        if ($originalNotes !== null && trim($originalNotes) !== '') {
            $parts[] = trim($originalNotes);
        }

        $line = '[' . now()->format('Y-m-d H:i') . '] ' . $operation;
        if ($newNote !== null && trim($newNote) !== '') {
            $line .= ': ' . trim($newNote);
        }

        $parts[] = $line;

        return implode("\n", $parts);
    }
}
