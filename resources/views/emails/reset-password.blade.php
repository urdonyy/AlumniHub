<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset your AlumniHub password</title>
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

                    {{-- ── Body ──────────────────────────────────────────────────── --}}
                    <tr>
                        <td style="padding:36px 32px 28px;">

                            {{-- Icon --}}
                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 auto 24px;">
                                <tr>
                                    <td align="center"
                                        style="width:64px; height:64px; background:#fef3c7; border-radius:50%; text-align:center; vertical-align:middle;">
                                        <span style="font-size:28px; line-height:64px;">🔒</span>
                                    </td>
                                </tr>
                            </table>

                            <h1 style="margin:0 0 8px; font-size:22px; font-weight:700; color:#111827; text-align:center;">
                                Reset your password
                            </h1>
                            <p style="margin:0 0 24px; font-size:14px; color:#6b7280; text-align:center; line-height:1.6;">
                                We received a request to reset the password for your account. Click the button below to choose a new password.
                            </p>

                            {{-- CTA Button --}}
                            <table role="presentation" cellspacing="0" cellpadding="0"
                                style="margin:0 auto 28px; width:100%;">
                                <tr>
                                    <td align="center"
                                        style="background:#7f1d1d; border-radius:8px;">
                                        <a href="{{ $resetUrl }}"
                                            style="display:block; padding:14px 32px; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700; letter-spacing:0.3px;">
                                            Reset Password
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            {{-- Expiry note --}}
                            <p style="margin:0 0 20px; font-size:13px; color:#9ca3af; text-align:center;">
                                This link expires in <strong>60 minutes</strong>.
                            </p>

                            {{-- Divider --}}
                            <hr style="border:none; border-top:1px solid #e5e7eb; margin:24px 0;">

                            {{-- Fallback URL --}}
                            <p style="margin:0 0 6px; font-size:13px; color:#6b7280;">
                                If the button doesn't work, copy and paste this link into your browser:
                            </p>
                            <p style="margin:0; font-size:12px; color:#7f1d1d; word-break:break-all; line-height:1.6;">
                                {{ $resetUrl }}
                            </p>
                        </td>
                    </tr>

                    {{-- ── Footer ─────────────────────────────────────────────────── --}}
                    <tr>
                        <td style="background:#f9fafb; border-top:1px solid #e5e7eb; padding:16px 32px; text-align:center;">
                            <p style="margin:0 0 4px; font-size:12px; color:#9ca3af;">
                                If you did not request a password reset, no further action is required.
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
