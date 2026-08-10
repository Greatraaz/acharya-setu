<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        :root {
            --navy: #0b1b3a;
            --amber: #f59e0b;
            --amber-dark: #d97706;
            --ink: #1e293b;
            --muted: #64748b;
            --line: #e2e8f0;
            --soft: #f8fafc;
            --amber-soft: #fffbeb;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(ellipse 80% 50% at 10% -10%, rgba(245, 158, 11, 0.14), transparent 55%),
                linear-gradient(180deg, #f1f5f9 0%, #eef2ff 100%);
            min-height: 100vh;
        }
        .wrap { max-width: 860px; margin: 28px auto; padding: 0 16px 48px; }
        .toolbar { display: flex; gap: 10px; justify-content: flex-end; margin-bottom: 14px; flex-wrap: wrap; }
        .btn {
            appearance: none;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--ink);
            padding: 9px 16px;
            border-radius: 10px;
            text-decoration: none;
            font: 600 13px/1.2 system-ui, sans-serif;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .btn:hover { border-color: #cbd5e1; }
        .btn-primary {
            background: linear-gradient(135deg, var(--amber), var(--amber-dark));
            border-color: transparent;
            color: #fff;
        }
        .sheet {
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.06);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
        }
        .accent { height: 7px; background: linear-gradient(90deg, var(--amber), #fbbf24 45%, var(--navy)); }
        .sheet-body { padding: 36px 40px 40px; }
        .top { display: flex; justify-content: space-between; gap: 28px; margin-bottom: 28px; align-items: flex-start; }
        .logo { height: 58px; width: auto; max-width: 230px; object-fit: contain; display: block; }
        .brand-fallback { font-size: 28px; font-weight: 800; color: var(--navy); letter-spacing: 0.02em; }
        .tagline { color: var(--amber-dark); font-size: 12px; margin-top: 6px; font-weight: 600; }
        .seller { color: var(--muted); font-size: 13px; line-height: 1.55; margin-top: 12px; }
        .meta { text-align: right; }
        .badge {
            display: inline-block;
            background: var(--amber-soft);
            color: #b45309;
            border: 1px solid #fcd34d;
            padding: 4px 10px;
            border-radius: 999px;
            font: 700 11px/1 system-ui, sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 10px;
        }
        h1 {
            margin: 0 0 10px;
            font-size: 26px;
            color: var(--navy);
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .meta-lines { font-size: 13px; line-height: 1.7; color: var(--muted); }
        .meta-lines strong { color: var(--navy); }
        .divider { height: 1px; background: var(--line); margin: 0 0 24px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 28px; }
        .box {
            background: var(--soft);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 16px 18px;
        }
        .box h3 {
            margin: 0 0 10px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            font-weight: 700;
        }
        .box p { margin: 0; font-size: 14px; line-height: 1.55; }
        .box strong { color: var(--navy); font-size: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 22px; overflow: hidden; border-radius: 12px; }
        th, td { padding: 14px 14px; text-align: left; font-size: 14px; }
        th {
            background: var(--navy);
            color: #fff;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
        }
        td { border-bottom: 1px solid var(--line); vertical-align: top; }
        tbody tr:nth-child(even) td { background: #f8fafc; }
        .right { text-align: right; }
        .muted { color: var(--muted); font-size: 13px; }
        .totals {
            width: min(300px, 100%);
            margin-left: auto;
            background: var(--soft);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 8px 18px 14px;
        }
        .totals .row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 14px;
            border-bottom: 1px solid var(--line);
            color: var(--muted);
        }
        .totals .row span:last-child { color: var(--ink); font-weight: 600; }
        .totals .row.grand {
            border-bottom: none;
            padding-top: 14px;
            font-size: 18px;
            font-weight: 800;
            color: var(--navy);
        }
        .totals .row.grand span:last-child { color: var(--amber-dark); font-size: 20px; }
        .foot {
            margin-top: 32px;
            padding-top: 18px;
            border-top: 1px solid var(--line);
            font-size: 12px;
            color: #94a3b8;
            text-align: center;
            line-height: 1.55;
        }
        .foot .slogan { color: var(--amber-dark); font-weight: 700; margin-bottom: 6px; }
        @media (max-width: 640px) {
            .sheet-body { padding: 24px 18px 28px; }
            .top { flex-direction: column; }
            .meta { text-align: left; }
            .grid { grid-template-columns: 1fr; }
        }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .wrap { margin: 0; max-width: none; padding: 0; }
            .sheet { border: none; border-radius: 0; box-shadow: none; }
            .sheet-body { padding: 0; }
        }
    </style>
</head>
<body @if(!empty($print)) onload="window.print()" @endif>
<div class="wrap">
    <div class="toolbar">
        @if(!empty($backUrl))
            <a class="btn" href="{{ $backUrl }}">Back</a>
        @endif
        @if(!empty($downloadUrl))
            <a class="btn btn-primary" href="{{ $downloadUrl }}">Download PDF</a>
        @endif
        <a class="btn" href="{{ $printUrl ?? '#' }}" @if(empty($printUrl)) onclick="window.print();return false;" @endif>Print</a>
    </div>

    <div class="sheet">
        <div class="accent"></div>
        <div class="sheet-body">
            <div class="top">
                <div>
                    @include('invoices.partials.brand-logo', ['forPdf' => false, 'invoice' => $invoice])
                    <div class="seller">
                        @if($invoice->seller_name && $invoice->seller_name !== 'Vedrix')
                            {{ $invoice->seller_name }}<br>
                        @endif
                        @if($invoice->seller_gstin) GSTIN: {{ $invoice->seller_gstin }}<br>@endif
                        @if($invoice->seller_address) {{ $invoice->seller_address }}<br>@endif
                        @if($invoice->seller_email) {{ $invoice->seller_email }}@endif
                        @if($invoice->seller_phone) · {{ $invoice->seller_phone }}@endif
                    </div>
                </div>
                <div class="meta">
                    <div class="badge">{{ ucfirst($invoice->status) }}</div>
                    <h1>Tax Invoice</h1>
                    <div class="meta-lines">
                        <div><strong>Invoice #</strong> {{ $invoice->invoice_number }}</div>
                        <div><strong>Date</strong> {{ $invoice->invoice_date?->format('d M Y') }}</div>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <div class="grid">
                <div class="box">
                    <h3>Bill To</h3>
                    <p>
                        <strong>{{ $invoice->billing_name ?: 'Mentee' }}</strong><br>
                        @if($invoice->billing_email) {{ $invoice->billing_email }}<br>@endif
                        @if($invoice->billing_phone) {{ $invoice->billing_phone }}@endif
                    </p>
                </div>
                <div class="box">
                    <h3>Subscription Period</h3>
                    <p>
                        <strong>
                            @if($invoice->subscription_starts_at && $invoice->subscription_expires_at)
                                {{ $invoice->subscription_starts_at->format('d M Y') }}
                                – {{ $invoice->subscription_expires_at->format('d M Y') }}
                            @else
                                —
                            @endif
                        </strong>
                        <br>
                        @if($invoice->payment_reference)
                            <span class="muted">Payment ref: {{ $invoice->payment_reference }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $invoice->plan_name }}</strong><br>
                            <span class="muted">Mentorship subscription plan</span>
                        </td>
                        <td class="right">{{ number_format((float) $invoice->base_amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="totals">
                <div class="row"><span>Taxable value</span><span>₹{{ number_format((float) $invoice->base_amount, 2) }}</span></div>
                @if((float) $invoice->cgst_percent > 0)
                <div class="row"><span>CGST ({{ rtrim(rtrim(number_format($invoice->cgst_percent, 2, '.', ''), '0'), '.') }}%)</span><span>₹{{ number_format((float) $invoice->cgst_amount, 2) }}</span></div>
                @endif
                @if((float) $invoice->sgst_percent > 0)
                <div class="row"><span>SGST ({{ rtrim(rtrim(number_format($invoice->sgst_percent, 2, '.', ''), '0'), '.') }}%)</span><span>₹{{ number_format((float) $invoice->sgst_amount, 2) }}</span></div>
                @endif
                <div class="row grand"><span>Total payable</span><span>₹{{ number_format((float) $invoice->total_amount, 2) }}</span></div>
            </div>

            <div class="foot">
                <div class="slogan">Mentors shape possibilities</div>
                This is a computer-generated invoice for your plan purchase.
                @if($invoice->razorpay_payment_id)
                    Razorpay payment ID: {{ $invoice->razorpay_payment_id }}.
                @endif
            </div>
        </div>
    </div>
</div>
</body>
</html>
