@extends('emails._layout')

@section('content')
    <p style="margin:0 0 10px;font-size:14px;">Dear {{ $booking->guest?->full_name ?? 'Guest' }},</p>
    <h1 style="margin:0 0 12px;font-size:22px;color:#92400e;">Outstanding Balance Reminder</h1>
    <p style="margin:0 0 14px;font-size:14px;line-height:1.6;">A balance is still outstanding for booking
        <strong>{{ $booking->booking_reference }}</strong>.</p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
        style="border-collapse:collapse;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
        <tr>
            <td style="padding:10px 12px;background:#fffbeb;font-size:13px;"><strong>Check-In Date</strong></td>
            <td style="padding:10px 12px;background:#fffbeb;font-size:13px;">{{ $booking->check_in_date?->format('Y-m-d') }}
            </td>
        </tr>
        <tr>
            <td style="padding:10px 12px;font-size:13px;"><strong>Outstanding Amount</strong></td>
            <td style="padding:10px 12px;font-size:13px;">RM {{ number_format($outstandingBalance, 2) }}</td>
        </tr>
    </table>

    <p style="margin:16px 0 0;font-size:13px;color:#334155;">Please settle the outstanding amount before check-in to ensure
        a smooth arrival.</p>
@endsection
