<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $headline }} — {{ $appName }}</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; background: #f3f4f6; }
        .message-body p { margin: 0 0 14px; color: #3f3f46; font-size: 15px; line-height: 1.75; }
        .message-body p:last-child { margin-bottom: 0; }
        .message-body h1, .message-body h2, .message-body h3 { color: #18181b; margin: 20px 0 8px; line-height: 1.3; font-weight: 700; }
        .message-body h1 { font-size: 22px; }
        .message-body h2 { font-size: 18px; }
        .message-body h3 { font-size: 16px; }
        .message-body a { color: #d97706; text-decoration: underline; }
        .message-body ul, .message-body ol { margin: 0 0 14px; padding-left: 22px; color: #3f3f46; }
        .message-body li { margin: 0 0 6px; line-height: 1.6; }
        .message-body blockquote { margin: 0 0 14px; padding: 12px 16px; border-left: 3px solid #f59e0b; background: #fffbeb; color: #78716c; }
        @media screen and (max-width: 620px) {
            .email-wrap { padding: 12px 8px !important; }
            .email-pad { padding: 22px 18px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
    <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;">
        {{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', html_entity_decode(str_ireplace(['&nbsp;', '&#160;', '&#xA0;'], ' ', strip_tags($bodyHtml)), ENT_QUOTES | ENT_HTML5, 'UTF-8'))), 90) }}
    </div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="email-wrap" style="background:#f3f4f6;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 8px 24px rgba(15,15,15,0.06);">
                    <tr>
                        <td style="height:6px;background:linear-gradient(90deg,#f59e0b,#fbbf24);font-size:0;line-height:0;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:22px 32px 18px;background:#111111;">
                            <img src="{{ $logoUrl }}" alt="{{ $appName }}" width="180" style="height:auto;max-width:180px;display:block;margin:0 auto;">
                        </td>
                    </tr>
                    <tr>
                        <td class="email-pad" style="padding:32px 36px 8px;">
                            <p style="margin:0 0 6px;font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#d97706;">
                                Message from {{ $appName }}
                            </p>
                            <h1 style="margin:0 0 18px;font-size:22px;line-height:1.35;color:#18181b;font-weight:800;">
                                {{ $headline }}
                            </h1>
                            <p style="margin:0 0 20px;font-size:16px;color:#27272a;font-weight:600;">
                                Hi {{ $firstName }},
                            </p>
                            <div class="message-body" style="color:#3f3f46;font-size:15px;line-height:1.75;">
                                {!! $bodyHtml !!}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" class="email-pad" style="padding:8px 36px 8px;">
                            <a href="https://vedrix.online/" style="display:inline-block;background:#f59e0b;color:#111111;text-decoration:none;font-size:14px;font-weight:700;padding:12px 22px;border-radius:10px;">
                                Open {{ $appName }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-pad" style="padding:20px 36px 28px;">
                            <p style="margin:0;font-size:14px;line-height:1.7;color:#71717a;">
                                Warm regards,<br>
                                <strong style="color:#27272a;">The {{ $appName }} Team</strong>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 32px;background:#fafafa;border-top:1px solid #f4f4f5;text-align:center;">
                            <p style="margin:0;font-size:12px;color:#71717a;">
                                © {{ date('Y') }} {{ $appName }}
                                &nbsp;·&nbsp; <a href="https://vedrix.online/" style="color:#d97706;text-decoration:none;">vedrix.online</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
