<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Invitation</title>
</head>
<body style="margin:0; padding:0; background:#f3f4f6; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
        style="background:#f3f4f6; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                    style="max-width:600px; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e5e7eb;">

                    {{-- ── Header ─────────────────────────────────────────────────── --}}
                    <tr>
                        <td style="background:#7f1d1d; padding:28px 32px; text-align:center;">
                            <img src="{{ $logoUrl }}" alt="AlumniHub"
                                style="height:44px; width:auto; display:block; margin:0 auto 10px; filter:brightness(0) invert(1);">
                            <p style="margin:0; font-size:20px; font-weight:700; color:#ffffff; letter-spacing:-0.3px;">
                                Alumni<span style="color:#fbbf24;">Hub</span>
                            </p>
                            <p style="margin:6px 0 0; font-size:12px; color:#fca5a5; letter-spacing:0.5px; text-transform:uppercase;">
                                PUP-ITECH Alumni Network
                            </p>
                        </td>
                    </tr>

                    {{-- ── Banner ─────────────────────────────────────────────────── --}}
                    <tr>
                        <td style="background:#eef2ff; border-bottom:1px solid #c7d2fe; padding:14px 32px; text-align:center;">
                            <p style="margin:0; font-size:14px; font-weight:700; color:#3730a3;">
                                📅 &nbsp;You're invited to an event
                            </p>
                        </td>
                    </tr>

                    {{-- ── Body ──────────────────────────────────────────────────── --}}
                    <tr>
                        <td style="padding:32px 32px 24px;">

                            <p style="margin:0 0 8px; font-size:16px; font-weight:600; color:#111827;">
                                Hello, {{ $recipientName }}
                            </p>

                            <p style="margin:0 0 20px; font-size:15px; line-height:1.7; color:#374151;">
                                <strong>{{ $actorName }}</strong> invited you to the following event on AlumniHub:
                            </p>

                            {{-- Event card --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                style="margin:0 0 24px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px;">
                                <tr>
                                    <td style="padding:20px 22px;">
                                        <p style="margin:0 0 12px; font-size:18px; font-weight:700; color:#111827;">
                                            {{ $eventName }}
                                        </p>

                                        <p style="margin:0 0 8px; font-size:14px; color:#374151;">
                                            <span style="color:#6b7280;">🕒 When:</span>
                                            {{ optional($startsAt)->format('M j, Y · g:i A') }}
                                            @if ($endsAt)
                                                &ndash; {{ $endsAt->format('M j, Y · g:i A') }}
                                            @endif
                                        </p>

                                        @if ($eventType === 'in_person')
                                            <p style="margin:0 0 8px; font-size:14px; color:#374151;">
                                                <span style="color:#6b7280;">📍 Where:</span>
                                                {{ $address }}@if ($venue) · {{ $venue }} @endif
                                            </p>
                                        @else
                                            <p style="margin:0 0 8px; font-size:14px; color:#374151;">
                                                <span style="color:#6b7280;">🌐 Format:</span> Online
                                            </p>
                                        @endif

                                        @if ($externalLink)
                                            <p style="margin:0 0 4px; font-size:14px; color:#374151; word-break:break-all;">
                                                <span style="color:#6b7280;">🔗 Link:</span>
                                                <a href="{{ $externalLink }}" style="color:#7f1d1d; text-decoration:underline;">{{ $externalLink }}</a>
                                            </p>
                                        @endif

                                        @if (!empty($description))
                                            <p style="margin:12px 0 0; font-size:14px; line-height:1.7; color:#4b5563; border-top:1px solid #e5e7eb; padding-top:12px;">
                                                {{ $description }}
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            {{-- CTA --}}
                            <table role="presentation" cellspacing="0" cellpadding="0" style="width:100%; margin-bottom:24px;">
                                <tr>
                                    <td align="center" style="background:#7f1d1d; border-radius:8px;">
                                        <a href="{{ $dashboardUrl }}"
                                            style="display:block; padding:14px 32px; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700; letter-spacing:0.3px;">
                                            View on AlumniHub
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0; font-size:14px; color:#6b7280; line-height:1.6;">
                                Regards,<br>
                                <strong style="color:#374151;">The {{ $appName }} Team</strong>
                            </p>
                        </td>
                    </tr>

                    {{-- ── Footer ─────────────────────────────────────────────────── --}}
                    <tr>
                        <td style="background:#f9fafb; border-top:1px solid #e5e7eb; padding:16px 32px; text-align:center;">
                            <p style="margin:0 0 4px; font-size:12px; color:#9ca3af;">
                                You received this email because you have an account on {{ $appName }}.
                            </p>
                            <p style="margin:0; font-size:12px; color:#d1d5db;">
                                © {{ date('Y') }} {{ $appName }} · PUP-ITECH
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
