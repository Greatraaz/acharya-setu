<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 28px 28px 36px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1e293b;
            font-size: 11px;
            margin: 0;
            padding: 0;
            line-height: 1.45;
        }
        .accent { height: 6px; background: #f59e0b; margin: 0 0 18px; }
        .top { width: 100%; margin-bottom: 18px; border-collapse: collapse; }
        .top td { vertical-align: top; }
        .logo { height: 52px; width: auto; max-width: 210px; }
        .brand-fallback {
            font-size: 22px;
            font-weight: bold;
            color: #0b1b3a;
        }
        .tagline { color: #d97706; font-size: 9px; margin-top: 4px; }
        .seller { color: #64748b; font-size: 9px; line-height: 1.5; margin-top: 8px; }
        .right { text-align: right; }
        .doc-title {
            font-size: 18px;
            font-weight: bold;
            color: #0b1b3a;
            margin: 0 0 6px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .badge {
            display: inline-block;
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fcd34d;
            padding: 2px 8px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .meta { color: #475569; font-size: 10px; line-height: 1.65; }
        .meta strong { color: #0b1b3a; }
        .divider { height: 1px; background: #e2e8f0; margin: 4px 0 16px; }
        .section { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .section td { width: 50%; vertical-align: top; }
        .card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px 14px;
            margin-right: 8px;
        }
        .card-right { margin-right: 0; margin-left: 8px; }
        .label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            margin-bottom: 6px;
            font-weight: bold;
        }
        .card strong { color: #0b1b3a; font-size: 12px; }
        .muted { color: #64748b; font-size: 10px; line-height: 1.5; }
        table.items { width: 100%; border-collapse: collapse; margin: 8px 0 14px; }
        table.items th {
            background: #0b1b3a;
            color: #ffffff;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 9px 10px;
        }
        table.items th.right { text-align: right; }
        table.items td {
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 10px;
            vertical-align: top;
        }
        .totals { width: 250px; margin-left: auto; border-collapse: collapse; }
        .totals td { padding: 7px 0; color: #475569; font-size: 10px; }
        .totals td.right { text-align: right; color: #1e293b; }
        .totals .grand td {
            border-top: 2px solid #f59e0b;
            padding-top: 10px;
            font-size: 13px;
            font-weight: bold;
            color: #0b1b3a;
        }
        .foot {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
            line-height: 1.55;
        }
        .foot .slogan { color: #d97706; font-weight: bold; margin-bottom: 4px; }
    </style>
</head>
<body>
<div class="accent"></div>

<table class="top">
    <tr>
        <td width="58%">
            @include('invoices.partials.brand-logo', ['forPdf' => true, 'invoice' => $invoice])
            <div class="seller">
                @if($invoice->seller_name && $invoice->seller_name !== 'Vedrix')
                    {{ $invoice->seller_name }}<br>
                @endif
                @if($invoice->seller_address) {{ $invoice->seller_address }}<br>@endif
                @if($invoice->seller_email) {{ $invoice->seller_email }}@endif
                @if($invoice->seller_phone) · {{ $invoice->seller_phone }}@endif
            </div>
        </td>
        <td class="right" width="42%">
            <div class="badge">{{ $invoice->paymentMethodLabel() }}</div>
            <div class="doc-title">Session Invoice</div>
            <div class="meta">
                <strong>Invoice #</strong> {{ $invoice->invoice_number }}<br>
                <strong>Date</strong> {{ $invoice->invoice_date?->format('d M Y') }}<br>
                <strong>Payment</strong> {{ $invoice->paymentMethodLabel() }}
            </div>
        </td>
    </tr>
</table>

<div class="divider"></div>

<table class="section">
    <tr>
        <td>
            <div class="card">
                <div class="label">Bill To</div>
                <strong>{{ $invoice->billing_name ?: 'Mentee' }}</strong>
                <div class="muted">
                    @if($invoice->billing_email) {{ $invoice->billing_email }}<br>@endif
                    @if($invoice->billing_phone) {{ $invoice->billing_phone }}@endif
                </div>
            </div>
        </td>
        <td>
            <div class="card card-right">
                <div class="label">Session Details</div>
                <strong>
                    @if($invoice->session_at)
                        {{ $invoice->session_at->format('d M Y · h:i A') }}
                    @else
                        Mentorship session
                    @endif
                </strong>
                <div class="muted">
                    {{ $invoice->duration_minutes }} min<br>
                    Booking: {{ $invoice->booking_ref ?: '—' }}
                </div>
            </div>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th>Description</th>
            <th class="right" width="28%">Amount (INR)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <strong>{{ $invoice->description ?: 'Mentorship session' }}</strong><br>
                <span class="muted">Paid via {{ $invoice->paymentMethodLabel() }} · GST not applicable</span>
            </td>
            <td class="right">{{ number_format((float) $invoice->total_amount, 2) }}</td>
        </tr>
    </tbody>
</table>

<table class="totals">
    @if((float) $invoice->wallet_amount > 0)
    <tr>
        <td>Wallet</td>
        <td class="right">{{ number_format((float) $invoice->wallet_amount, 2) }}</td>
    </tr>
    @endif
    @if((float) $invoice->razorpay_amount > 0)
    <tr>
        <td>Razorpay</td>
        <td class="right">{{ number_format((float) $invoice->razorpay_amount, 2) }}</td>
    </tr>
    @endif
    <tr class="grand">
        <td>Total</td>
        <td class="right">₹ {{ number_format((float) $invoice->total_amount, 2) }}</td>
    </tr>
</table>

<div class="foot">
    <div class="slogan">Mentors shape possibilities</div>
    Computer-generated session invoice. GST is not applicable on session bookings.
    @if($invoice->payment_reference) Ref: {{ $invoice->payment_reference }}. @endif
</div>
</body>
</html>
