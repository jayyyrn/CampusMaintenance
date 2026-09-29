<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Code</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1f5f9; padding:40px 16px;">
        <tr>
            <td align="center">

                {{-- Main Card --}}
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px; background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 10px 25px rgba(0,0,0,0.08);">

                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #4f46e5 0%, #14b8a6 100%); padding:32px 40px; text-align:center;">
                            <div style="display:inline-block; width:56px; height:56px; background-color:rgba(255,255,255,0.15); border-radius:14px; line-height:56px; font-size:28px;">
                                🔧
                            </div>
                            <h1 style="margin:16px 0 0 0; font-size:22px; color:#ffffff; font-weight:700; letter-spacing:-0.5px;">
                                CampusFix
                            </h1>
                            <p style="margin:4px 0 0 0; font-size:13px; color:rgba(255,255,255,0.85);">
                                Campus Maintenance &amp; Inventory
                            </p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:40px; text-align:center;">

                            <h2 style="margin:0 0 8px 0; font-size:22px; color:#0f172a; font-weight:700;">
                                Your Password Reset Code
                            </h2>
                            <p style="margin:0 0 24px 0; font-size:15px; color:#64748b; line-height:1.6;">
                                Hi {{ $fullName }},<br>
                                Use the code below to reset your CampusFix password.
                            </p>

                            {{-- Code Box --}}
                            <table cellpadding="0" cellspacing="0" border="0" style="margin:0 auto 24px auto;">
                                <tr>
                                    <td style="background-color:#eef2ff; border:2px dashed #4f46e5; border-radius:12px; padding:20px 32px;">
                                        <span style="font-family: 'Courier New', monospace; font-size:36px; font-weight:700; color:#4f46e5; letter-spacing:10px;">
                                            {{ $code }}
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            {{-- Expiry Notice --}}
                            <p style="margin:0 0 24px 0; font-size:13px; color:#64748b;">
                                ⏱ This code expires in <strong>{{ $expiresInMinutes }} minutes</strong>.
                            </p>

                            {{-- Divider --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:32px 0;">
                                <tr><td style="border-top:1px solid #e2e8f0;"></td></tr>
                            </table>

                            {{-- Warning --}}
                            <p style="margin:0; font-size:12px; color:#94a3b8; line-height:1.6;">
                                If you didn't request this, you can safely ignore this email.
                                Your password won't change until you enter the code on CampusFix.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color:#f8fafc; padding:20px 40px; text-align:center; border-top:1px solid #e2e8f0;">
                            <p style="margin:0; font-size:11px; color:#94a3b8;">
                                © {{ date('Y') }} CampusFix — Integrative Programming &amp; Technologies
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>