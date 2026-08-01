@php($appName = config('app.name', 'DFahMy Eco Resort'))
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? $appName }}</title>
</head>

<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0"
                    style="max-width:640px;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;">
                    <tr>
                        <td style="background:linear-gradient(120deg,#065f46,#10b981);padding:18px 24px;">
                            <p style="margin:0;font-size:20px;font-weight:700;color:#ecfdf5;letter-spacing:.3px;">
                                {{ $appName }}</p>
                            <p style="margin:6px 0 0;font-size:12px;color:#d1fae5;">Guest Communications</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px;background:#f8fafc;border-top:1px solid #e2e8f0;">
                            <p style="margin:0;font-size:12px;color:#475569;">This is an automated message from
                                {{ $appName }}.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
