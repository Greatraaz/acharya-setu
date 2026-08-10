<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        :root { --ink:#111827; --muted:#6b7280; --line:#e5e7eb; --brand:#b45309; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: Georgia, 'Times New Roman', serif; color:var(--ink); background:#f8fafc; }
        .wrap { max-width:820px; margin:24px auto; padding:0 16px 40px; }
        .toolbar { display:flex; gap:10px; justify-content:flex-end; margin-bottom:14px; }
        .btn { appearance:none; border:1px solid var(--line); background:#fff; color:var(--ink); padding:8px 14px; border-radius:8px; text-decoration:none; font:600 13px/1.2 system-ui,sans-serif; cursor:pointer; }
        .btn-primary { background:var(--brand); border-color:var(--brand); color:#fff; }
        .sheet { background:#fff; border:1px solid var(--line); padding:36px 40px; }
        .top { display:flex; justify-content:space-between; gap:24px; margin-bottom:28px; }
        .brand { font-size:28px; font-weight:700; letter-spacing:.02em; }
        .muted { color:var(--muted); font-size:13px; line-height:1.5; }
        h1 { margin:0 0 6px; font-size:26px; }
        .meta { text-align:right; font-size:13px; line-height:1.6; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:28px; }
        .box h3 { margin:0 0 8px; font-size:12px; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); font-family:system-ui,sans-serif; }
        .box p { margin:0; font-size:14px; line-height:1.55; }
        table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        th, td { padding:12px 10px; border-bottom:1px solid var(--line); text-align:left; font-size:14px; }
        th { font-family:system-ui,sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); }
        .right { text-align:right; }
        .totals { width:280px; margin-left:auto; }
        .totals .row { display:flex; justify-content:space-between; padding:8px 0; font-size:14px; border-bottom:1px solid var(--line); }
        .totals .row.grand { font-size:18px; font-weight:700; border-bottom:none; padding-top:12px; }
        .foot { margin-top:28px; font-size:12px; color:var(--muted); line-height:1.5; }
        @media print {
            body { background:#fff; }
            .toolbar { display:none !important; }
            .wrap { margin:0; max-width:none; padding:0; }
            .sheet { border:none; padding:0; }
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
        <div class="top">
            <div>
                <div class="brand">{{ $invoice->seller_name ?: 'Vedrix' }}</div>
                <div class="muted">
                    @if($invoice->seller_gstin) GSTIN: {{ $invoice->seller_gstin }}<br>@endif
                    @if($invoice->seller_address) {{ $invoice->seller_address }}<br>@endif
                    @if($invoice->seller_email) {{ $invoice->seller_email }}@endif
                    @if($invoice->seller_phone) · {{ $invoice->seller_phone }}@endif
                </div>
            </div>
            <div class="meta">
                <h1>Tax Invoice</h1>
                <div><strong>Invoice #</strong> {{ $invoice->invoice_number }}</div>
                <div><strong>Date</strong> {{ $invoice->invoice_date?->format('d M Y') }}</div>
                <div><strong>Status</strong> {{ ucfirst($invoice->status) }}</div>
            </div>
        </div>

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
                <h3>Subscription</h3>
                <p>
                    @if($invoice->subscription_starts_at && $invoice->subscription_expires_at)
                        {{ $invoice->subscription_starts_at->format('d M Y') }}
                        → {{ $invoice->subscription_expires_at->format('d M Y') }}
                    @else
                        —
                    @endif
                    <br>
                    @if($invoice->payment_reference)
                        Payment ref: {{ $invoice->payment_reference }}
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
            <div class="row grand"><span>Total</span><span>₹{{ number_format((float) $invoice->total_amount, 2) }}</span></div>
        </div>

        <div class="foot">
            This is a computer-generated invoice for your plan purchase.
            @if($invoice->razorpay_payment_id)
                Razorpay payment ID: {{ $invoice->razorpay_payment_id }}.
            @endif
        </div>
    </div>
</div>
</body>
</html>
