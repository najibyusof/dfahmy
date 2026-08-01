@extends('emails._layout')

@section('content')
    <p style="margin:0 0 10px;font-size:14px;">Dear {{ $booking->guest?->full_name ?? 'Guest' }},</p>
    <h1 style="margin:0 0 12px;font-size:22px;color:#065f46;">Payment Receipt</h1>
    <p style="margin:0 0 14px;font-size:14px;line-height:1.6;">Thank you. We have received your payment for booking
        <strong>{{ $booking->booking_reference }}</strong>.</p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
        style="border-collapse:collapse;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
        <tr>
            <td style="padding:10px 12px;background:#f0fdf4;font-size:13px;"><strong>Receipt Number</strong></td>
            <td style="padding:10px 12px;background:#f0fdf4;font-size:13px;">{{ $payment->receipt_number }}</td>
        </tr>
        <tr>
            <td style="padding:10px 12px;font-size:13px;"><strong>Payment Date</strong></td>
            <td style="padding:10px 12px;font-size:13px;">{{ $payment->payment_date?->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td style="padding:10px 12px;background:#f0fdf4;font-size:13px;"><strong>Amount</strong></td>
            <td style="padding:10px 12px;background:#f0fdf4;font-size:13px;">RM
                {{ number_format((float) $payment->amount, 2) }}</td>
        </tr>
        <tr>
            <td style="padding:10px 12px;font-size:13px;"><strong>Method</strong></td>
            <td style="padding:10px 12px;font-size:13px;">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
        </tr>
    </table>

    <p style="margin:16px 0 0;font-size:13px;color:#334155;">If you need assistance, contact our reception team.</p>
@endsection
