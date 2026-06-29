<!doctype html>
<html>
    <head>
        <meta charset="UTF-8" />
        <style>
            body {
                font-family: "Segoe UI", Arial, sans-serif;
                background: #f4f4f5;
                color: #09090b;
            }
            .wrap {
                max-width: 580px;
                margin: 30px auto;
                background: #fff;
                border-radius: 12px;
                overflow: hidden;
                border: 1px solid #e4e4e7;
            }
            .header {
                background: #09090b;
                padding: 24px 32px;
                color: #f59e0b;
                font-size: 18px;
                font-weight: 800;
            }
            .body {
                padding: 32px;
            }
            .row {
                margin-bottom: 16px;
                padding-bottom: 16px;
                border-bottom: 1px solid #f4f4f5;
            }
            .label {
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: #71717a;
                margin-bottom: 4px;
            }
            .value {
                font-size: 14px;
                color: #09090b;
            }
            .msg {
                background: #f9fafb;
                border-left: 3px solid #f59e0b;
                padding: 16px;
                border-radius: 6px;
                font-size: 14px;
                line-height: 1.75;
                color: #374151;
            }
        </style>
    </head>
    <body>
        <div class="wrap">
            <div class="header">📬 New Contact Message — AcharyaSetu</div>
            <div class="body">
                <div class="row">
                    <div class="label">From</div>
                    <div class="value">{{ $data['name'] }} &lt;{{ $data['email'] }}&gt;</div>
                </div>
                <div class="row">
                    <div class="label">Subject</div>
                    <div class="value">{{ $data['subject'] }}</div>
                </div>
                <div class="row">
                    <div class="label">Message</div>
                    <div class="msg">{{ nl2br(e($data['message'])) }}</div>
                </div>
            </div>
        </div>
    </body>
</html>
