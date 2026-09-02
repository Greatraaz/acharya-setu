<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('email_title', 'Vedrix')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background: #0f0f0f;
            color: #fff;
        }
        .wrapper {
            max-width: 560px;
            margin: 40px auto;
            background: #1a1a1a;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            padding: 28px 32px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            text-align: center;
        }
        .logo img {
            height: 56px;
            width: auto;
            max-width: 240px;
            object-fit: contain;
            display: inline-block;
        }
        .body { padding: 32px; }
        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #f59e0b;
            margin-bottom: 10px;
        }
        .greeting {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 10px;
            line-height: 1.3;
        }
        .desc {
            font-size: 14px;
            color: #a1a1aa;
            line-height: 1.75;
            margin-bottom: 24px;
        }
        .details {
            background: #0f0f0f;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 12px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            font-size: 13px;
        }
        .details-row:last-child { border-bottom: none; }
        .details-label {
            color: #71717a;
            flex-shrink: 0;
        }
        .details-value {
            color: #fff;
            text-align: right;
            word-break: break-word;
        }
        .note {
            background: rgba(245, 158, 11, 0.08);
            border: 1px solid rgba(245, 158, 11, 0.22);
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 13px;
            color: #fcd34d;
            line-height: 1.55;
            margin-bottom: 24px;
        }
        .btn-wrap { text-align: center; margin-bottom: 24px; }
        .btn {
            display: inline-block;
            background: #f59e0b;
            color: #000 !important;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            padding: 12px 22px;
            border-radius: 10px;
        }
        .signoff {
            font-size: 13px;
            color: #71717a;
            line-height: 1.7;
        }
        .footer {
            background: #111;
            padding: 22px 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.07);
            text-align: center;
        }
        .footer p {
            font-size: 12px;
            color: #52525b;
            line-height: 1.7;
        }
        .footer a {
            color: #f59e0b;
            text-decoration: none;
        }
        @media (max-width: 560px) {
            .wrapper { margin: 0; border-radius: 0; }
            .body, .header, .footer { padding: 24px 18px; }
            .details-row { flex-direction: column; gap: 4px; }
            .details-value { text-align: left; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <div class="logo">
                <img src="{{ $logoUrl ?? url('frontend/images/logo.png') }}" alt="Vedrix" width="220" height="56">
            </div>
        </div>
        <div class="body">
            @yield('content')
        </div>
        <div class="footer">
            <p>
                © {{ date('Y') }} Vedrix &nbsp;|&nbsp;
                <a href="{{ config('app.url') }}">{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'vedrix.com' }}</a>
            </p>
            <p style="margin-top: 6px;">This is an automated message. Please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>
