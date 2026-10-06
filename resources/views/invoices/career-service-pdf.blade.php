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
        .accent { height: 5px; background: #f59e0b; margin: 0 0 16px; }
        .top { width: 100%; margin-bottom: 14px; border-collapse: collapse; }
        .top td { vertical-align: top; }
        .logo { height: 42px; width: auto; max-width: 180px; display: block; }
        .brand-fallback {
            font-size: 20px;
            font-weight: bold;
            color: #0b1b3a;
            letter-spacing: 0.02em;
        }
        .tagline { color: #d97706; font-size: 9px; margin-top: 3px; }
        .seller { color: #64748b; font-size: 9px; line-height: 1.5; margin-top: 6px; }
        .right { text-align: right; }
        .doc-title {
            font-size: 16px;
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
        .divider { height: 1px; background: #e2e8f0; margin: 2px 0 14px; }
        .section { width: 100%; margin-bottom: 14px; border-collapse: collapse; }
        .section td { width: 50%; vertical-align: top; padding: 0; }
        .card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 11px 12px;
        }
        .card-left { margin-right: 6px; }
        .card-right { margin-left: 6px; }
        .label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .card strong { color: #0b1b3a; font-size: 12px; }
        .muted { color: #64748b; font-size: 10px; line-height: 1.5; }
        table.items { width: 100%; border-collapse: collapse; margin: 4px 0 12px; }
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
            padding: 11px 10px;
            vertical-align: top;
            background: #ffffff;
        }
        .amt { white-space: nowrap; }
        .totals-wrap { width: 100%; }
        .totals {
            width: 240px;
            margin-left: auto;
            border-collapse: collapse;
        }
        .totals td { padding: 6px 0; color: #475569; font-size: 10px; }
        .totals td.right { text-align: right; color: #1e293b; }
        .totals .grand td {
            border-top: 2px solid #f59e0b;
            padding-top: 10px;
            font-size: 13px;
            font-weight: bold;
            color: #0b1b3a;
        }
        .foot {
            margin-top: 24px;
            padding-top: 10px;
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
@php
    $isPlanCovered = ($invoice->payment_method ?? '') === 'plan' || (float) $invoice->total_amount <= 0;
    $paymentNote = $isPlanCovered
        ? 'Covered by plan · GST not applicable'
        : ('Paid via '.$invoice->paymentMethodLabel().' · GST not applicable');
@endphp
<div class="accent"></div>

<table class="top">
    <tr>
        <td width="55%">
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
        <td class="right" width="45%">
            <div class="badge">{{ $isPlanCovered ? 'Plan benefit' : $invoice->paymentMethodLabel() }}</div>
            <div class="doc-title">Career Service Invoice</div>
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
            <div class="card card-left">
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
                <div class="label">Service</div>
                <strong>{{ $invoice->serviceLabel() }}</strong>
                <div class="muted">
                    Request #{{ $invoice->career_service_request_id }}
                    @if($invoice->plan_slug)<br>Plan: {{ ucfirst($invoice->plan_slug) }}@endif
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
                <strong>{{ $invoice->description ?: $invoice->serviceLabel() }}</strong><br>
                <span class="muted">{{ $paymentNote }}</span>
            </td>
            <td class="right amt">₹ {{ number_format((float) $invoice->total_amount, 2) }}</td>
        </tr>
    </tbody>
</table>

<div class="totals-wrap">
    <table class="totals">
        @if((float) ($invoice->wallet_amount ?? 0) > 0)
        <tr>
            <td>Wallet</td>
            <td class="right amt">₹ {{ number_format((float) $invoice->wallet_amount, 2) }}</td>
        </tr>
        @endif
        @if((float) ($invoice->razorpay_amount ?? 0) > 0)
        <tr>
            <td>Razorpay</td>
            <td class="right amt">₹ {{ number_format((float) $invoice->razorpay_amount, 2) }}</td>
        </tr>
        @endif
        <tr class="grand">
            <td>Total paid</td>
            <td class="right amt">₹ {{ number_format((float) $invoice->total_amount, 2) }}</td>
        </tr>
    </table>
</div>

<div class="foot">
    <div class="slogan">Mentors shape possibilities</div>
    Computer-generated career service invoice. GST is not applicable on career add-ons.
    @if($invoice->payment_reference) Ref: {{ $invoice->payment_reference }}. @endif
</div>
</body>
</html>
