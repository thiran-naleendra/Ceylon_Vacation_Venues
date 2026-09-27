<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $heading }}</title>
</head>
<body style="margin:0;background:#f1f5f9;color:#0f172a;font-family:Arial,Helvetica,sans-serif;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">A new {{ strtolower($heading) }} has been received.</div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f1f5f9;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 8px 30px rgba(15,23,42,.08);">
                    <tr>
                        <td style="background:#062d50;padding:30px 32px;color:#ffffff;">
                            <div style="font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#67e8f9;">Ceylon Vacation Venues</div>
                            <h1 style="margin:10px 0 0;font-size:28px;line-height:1.25;">{{ $heading }}</h1>
                            <p style="margin:10px 0 0;color:#bae6fd;font-size:15px;line-height:1.6;">A new customer submission is ready for review.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                                @foreach($details as $label => $value)
                                    <tr>
                                        <th scope="row" align="left" valign="top" style="width:34%;padding:12px 14px 12px 0;border-bottom:1px solid #e2e8f0;color:#475569;font-size:13px;line-height:1.5;">{{ $label }}</th>
                                        <td valign="top" style="padding:12px 0;border-bottom:1px solid #e2e8f0;color:#0f172a;font-size:14px;line-height:1.6;white-space:pre-wrap;word-break:break-word;">{{ $value }}</td>
                                    </tr>
                                @endforeach
                            </table>
                            <div style="padding-top:28px;text-align:center;">
                                <a href="{{ $adminUrl }}" style="display:inline-block;background:#0b6b88;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 24px;border-radius:10px;">View in Admin Panel</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f8fafc;padding:20px 32px;color:#64748b;font-size:12px;line-height:1.6;text-align:center;">
                            This automated notification was sent by Ceylon Vacation Venues.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
