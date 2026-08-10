<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; margin: 0; padding: 24px; }
        .brand { font-size: 22px; font-weight: bold; margin-bottom: 4px; }
        .muted { color: #6b7280; font-size: 11px; line-height: 1.45; }
        .top { width: 100%; margin-bottom: 22px; }
        .top td { vertical-align: top; }
        .right { text-align: right; }
        h1 { font-size: 20px; margin: 0 0 8px; }
        .section { width: 100%; margin-bottom: 20px; }
        .section td { width: 50%; vertical-align: top; padding-right: 12px; }
        .label { font-size: 10px; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; margin-bottom: 6px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.items th { text-align: left; font-size: 10px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; padding: 8px 6px; }
        table.items td { border-bottom: 1px solid #e5e7eb; padding: 10px 6px; vertical-align: top; }
        .totals { width: 280px; margin-left: auto; }
        .totals td { padding: 6px 0; }
        .totals .grand td { font-size: 14px; font-weight: bold; padding-top: 10px; }
        .foot { margin-top: 24px; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    <table class="top">
        <tr>
            <td>
                <div class="brand">{{ $invoice->seller_name ?: 'Vedrix' }}</div>
                <div class="muted">
                    @if($invoice->seller_gstin) GSTIN: {{ $invoice->seller_gstin }}<br>@endif
                    @if($invoice->seller_address) {{ $invoice->seller_address }}<br>@endif
                    @if($invoice->seller_email) {{ $invoice->seller_email }}@endif
                    @if($invoice->seller_phone) · {{ $invoice->seller_phone }}@endif
                </div>
            </td>
            <td class="right">
                <h1>Session Invoice</h1>
                <div><strong>Invoice #</strong> {{ $invoice->invoice_number }}</div>
                <div><strong>Date</strong> {{ $invoice->invoice_date?->format('d M Y') }}</div>
                <div><strong>Payment</strong> {{ $invoice->paymentMethodLabel() }}</div>
            </td>
        </tr>
    </table>

    <table class="section">
        <tr>
            <td>
                <div class="label">Bill To</div>
                <div><strong>{{ $invoice->billing_name ?: 'Mentee' }}</strong></div>
                <div class="muted">
                    @if($invoice->billing_email) {{ $invoice->billing_email }}<br>@endif
                    @if($invoice->billing_phone) {{ $invoice->billing_phone }}@endif
                </div>
            </td>
            <td>
                <div class="label">Session</div>
                <div class="muted">
                    {{ $invoice->description }}<br>
                    @if($invoice->session_at) {{ $invoice->session_at->format('d M Y h:i A') }} · @endif
                    {{ $invoice->duration_minutes }} min<br>
                    Booking: {{ $invoice->booking_ref ?: '—' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th class="right">Amount (INR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $invoice->description ?: 'Mentorship session' }}</strong><br>
                    <span class="muted">Paid via {{ $invoice->paymentMethodLabel() }}</span>
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
            <td class="right">{{ number_format((float) $invoice->total_amount, 2) }}</td>
        </tr>
    </table>

    <div class="foot">
        Computer-generated session invoice.
        @if($invoice->payment_reference) Ref: {{ $invoice->payment_reference }}. @endif
    </div>
</body>
</html>
