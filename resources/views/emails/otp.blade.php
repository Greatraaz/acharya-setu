<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Your OTP — AcharyaSetu</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: "Segoe UI", Arial, sans-serif;
                background: #0f0f0f;
                color: #fff;
            }
            .wrapper {
                max-width: 520px;
                margin: 40px auto;
                background: #1a1a1a;
                border-radius: 16px;
                overflow: hidden;
                border: 1px solid rgba(255, 255, 255, 0.08);
            }
            .header {
                background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
                padding: 32px 40px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.07);
                text-align: center;
            }
            .logo {
                font-size: 22px;
                font-weight: 800;
                color: #f59e0b;
                letter-spacing: -0.5px;
            }
            .logo span {
                color: #fff;
            }
            .body {
                padding: 40px;
            }
            .greeting {
                font-size: 20px;
                font-weight: 700;
                margin-bottom: 10px;
            }
            .desc {
                font-size: 14px;
                color: #a1a1aa;
                line-height: 1.75;
                margin-bottom: 28px;
            }
            .otp-box {
                background: #0f0f0f;
                border: 2px solid #f59e0b;
                border-radius: 12px;
                padding: 24px;
                text-align: center;
                margin-bottom: 28px;
            }
            .otp-label {
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.1em;
                text-transform: uppercase;
                color: #a1a1aa;
                margin-bottom: 10px;
            }
            .otp-code {
                font-size: 42px;
                font-weight: 900;
                color: #f59e0b;
                letter-spacing: 8px;
                font-family: "Courier New", monospace;
            }
            .otp-expiry {
                font-size: 12px;
                color: #71717a;
                margin-top: 8px;
            }
            .warning {
                background: rgba(239, 68, 68, 0.08);
                border: 1px solid rgba(239, 68, 68, 0.2);
                border-radius: 8px;
                padding: 12px 16px;
                font-size: 13px;
                color: #fca5a5;
                margin-bottom: 24px;
            }
            .footer {
                background: #111;
                padding: 24px 40px;
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
            @media (max-width: 520px) {
                .wrapper {
                    margin: 0;
                    border-radius: 0;
                }
                .body,
                .header,
                .footer {
                    padding: 24px;
                }
                .otp-code {
                    font-size: 32px;
                    letter-spacing: 6px;
                }
            }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <div class="header">
                <div class="logo">Acharya<span>Setu</span></div>
            </div>
            <div class="body">
                <div class="greeting">
                    @if($type === 'registration') Verify your email address @elseif($type === 'login') Your login OTP
                    @else Reset your password @endif
                </div>
                <p class="desc">
                    @if($type === 'registration') Thanks for signing up! Enter the OTP below to verify your email and
                    complete registration. @elseif($type === 'login') Use this OTP to sign in to your AcharyaSetu
                    account. @else Use this OTP to reset your AcharyaSetu account password. @endif
                </p>

                <div class="otp-box">
                    <div class="otp-label">Your One-Time Password</div>
                    <div class="otp-code">{{ $otp }}</div>
                    <div class="otp-expiry">⏳ Valid for <strong>10 minutes</strong> only</div>
                </div>

                <div class="warning">
                    🔒 <strong>Never share this OTP</strong> with anyone — including AcharyaSetu team members. We will
                    never ask for your OTP.
                </div>

                <p style="font-size: 13px; color: #71717a; line-height: 1.7">
                    If you didn't request this, please ignore this email. Your account is safe.<br /><br />
                    — The AcharyaSetu Team
                </p>
            </div>
            <div class="footer">
                <p>
                    © {{ date('Y') }} AcharyaSetu &nbsp;|&nbsp;
                    <a href="{{ config('app.url') }}">acharyasetu.com</a> &nbsp;|&nbsp;
                    <a href="mailto:hello@acharyasetu.com">hello@acharyasetu.com</a>
                </p>
                <p style="margin-top: 6px">Bangalore, India 🇮🇳</p>
            </div>
        </div>
    </body>
</html>
