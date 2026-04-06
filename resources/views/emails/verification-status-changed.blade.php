<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alumni Verification Update</title>
</head>

<body style="margin:0; padding:0; background:#f4f6fb; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
        style="background:#f4f6fb; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                    style="max-width:640px; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e5e7eb;">
                    <tr>
                        <td style="background:#111827; padding:20px 24px; text-align:center;">
                            <img src="{{ $logoUrl }}" alt="AlumniHub Logo"
                                style="height:48px; width:auto; display:block; margin:0 auto 10px;">
                            <p style="margin:0; color:#f9fafb; font-size:14px; letter-spacing:0.3px;">{{ $appName }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 24px;">
                            <h1 style="margin:0 0 14px; font-size:24px; line-height:1.3; color:#111827;">Alumni
                                Verification Result</h1>
                            <p style="margin:0 0 16px; font-size:16px; line-height:1.6;">Hello {{ $name }},</p>

                            @if ($isApproved)
                                <p style="margin:0 0 16px; font-size:16px; line-height:1.6;">Your alumni verification has
                                    been <strong>approved</strong>.</p>
                            @else
                                <p style="margin:0 0 16px; font-size:16px; line-height:1.6;">Your alumni verification has
                                    been <strong>rejected</strong>.</p>
                            @endif

                            <div
                                style="margin:18px 0; padding:14px 16px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; font-size:15px;">
                                <strong>Status:</strong> {{ $statusLabel }}
                            </div>

                            @if (!empty($notes))
                                <div
                                    style="margin:18px 0; padding:14px 16px; background:#fffbeb; border:1px solid #fde68a; border-radius:10px;">
                                    <p style="margin:0 0 8px; font-size:14px;"><strong>Admin Notes</strong></p>
                                    <p style="margin:0; font-size:14px; line-height:1.6;">{{ $notes }}</p>
                                </div>
                            @endif

                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin:24px 0 12px;">
                                <tr>
                                    <td align="center" style="border-radius:8px; background:#8b1e1e;">
                                        <a href="{{ $dashboardUrl }}"
                                            style="display:inline-block; padding:12px 22px; color:#ffffff; text-decoration:none; font-size:14px; font-weight:700;">Open
                                            AlumniHub</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0; font-size:14px; color:#6b7280;">Regards,<br>{{ $appName }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>