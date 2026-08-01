@extends('emails._layout')

@section('content')
    <p style="margin:0 0 10px;font-size:14px;">Dear {{ $booking->guest?->full_name ?? 'Guest' }},</p>
    <h1 style="margin:0 0 12px;font-size:22px;color:#065f46;">Your Booking Is Confirmed</h1>
    <p style="margin:0 0 14px;font-size:14px;line-height:1.6;">We are pleased to confirm booking
        <strong>{{ $booking->booking_reference }}</strong>.</p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
        style="border-collapse:collapse;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
        <tr>
            <td style="padding:10px 12px;background:#f0fdf4;font-size:13px;"><strong>Check-In</strong></td>
            <td style="padding:10px 12px;background:#f0fdf4;font-size:13px;">{{ $booking->check_in_date?->format('Y-m-d') }}
            </td>
        </tr>
        <tr>
            <td style="padding:10px 12px;font-size:13px;"><strong>Check-Out</strong></td>
            <td style="padding:10px 12px;font-size:13px;">{{ $booking->check_out_date?->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td style="padding:10px 12px;background:#f0fdf4;font-size:13px;"><strong>Total Amount</strong></td>
            <td style="padding:10px 12px;background:#f0fdf4;font-size:13px;">RM
                {{ number_format((float) $booking->total_amount, 2) }}</td>
        </tr>
    </table>

    <p style="margin:16px 0 0;font-size:13px;color:#334155;">We look forward to welcoming you to DFahMy Eco Resort.</p>
@endsection
