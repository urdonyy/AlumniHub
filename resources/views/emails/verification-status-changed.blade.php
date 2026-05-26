<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alumni Verification Update</title>
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

                    {{-- ── Status banner ──────────────────────────────────────────── --}}
                    @if ($isApproved)
                    <tr>
                        <td style="background:#dcfce7; border-bottom:1px solid #bbf7d0; padding:14px 32px; text-align:center;">
                            <p style="margin:0; font-size:14px; font-weight:700; color:#15803d;">
                                ✅ &nbsp;Verification Approved
                            </p>
                        </td>
                    </tr>
                    @else
                    <tr>
                        <td style="background:#fee2e2; border-bottom:1px solid #fecaca; padding:14px 32px; text-align:center;">
                            <p style="margin:0; font-size:14px; font-weight:700; color:#b91c1c;">
                                ❌ &nbsp;Verification Rejected
                            </p>
                        </td>
                    </tr>
                    @endif

                    {{-- ── Body ──────────────────────────────────────────────────── --}}
                    <tr>
                        <td style="padding:32px 32px 24px;">

                            <p style="margin:0 0 8px; font-size:16px; font-weight:600; color:#111827;">
                                Hello, {{ $name }}
                            </p>

                            @if ($isApproved)
                                <p style="margin:0 0 16px; font-size:15px; line-height:1.7; color:#374151;">
                                    Great news! Your alumni verification has been <strong>approved</strong>. You now have full access to AlumniHub — connect with fellow alumni, join communities, and explore everything the platform has to offer.
                                </p>
                            @else
                                <p style="margin:0 0 16px; font-size:15px; line-height:1.7; color:#374151;">
                                    Unfortunately, your alumni verification was <strong>not approved</strong> this time. Please review the feedback below and re-submit with the correct documents.
                                </p>
                            @endif

                            {{-- Admin notes --}}
                            @if (!empty($notes))
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                    style="margin:0 0 24px; background:#fffbeb; border:1px solid #fde68a; border-radius:10px;">
                                    <tr>
                                        <td style="padding:16px 18px;">
                                            <p style="margin:0 0 6px; font-size:13px; font-weight:700; color:#92400e; text-transform:uppercase; letter-spacing:0.4px;">
                                                Admin Notes
                                            </p>
                                            <p style="margin:0; font-size:14px; line-height:1.7; color:#78350f;">
                                                {{ $notes }}
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            {{-- CTA --}}
                            <table role="presentation" cellspacing="0" cellpadding="0" style="width:100%; margin-bottom:24px;">
                                <tr>
                                    <td align="center" style="background:#7f1d1d; border-radius:8px;">
                                        <a href="{{ $dashboardUrl }}"
                                            style="display:block; padding:14px 32px; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700; letter-spacing:0.3px;">
                                            Open AlumniHub
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
