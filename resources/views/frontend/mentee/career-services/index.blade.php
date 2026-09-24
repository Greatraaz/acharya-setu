@extends('frontend.layouts.app')
@section('title', 'Career Services — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header flex-between" style="gap:12px;flex-wrap:wrap;">
            <div>
                <div class="dash-title">Career Services</div>
                <div class="dash-subtitle">Resume development & LinkedIn profile optimisation — reviewed by Vedrix.</div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;"><span class="alert-icon">✓</span><div style="font-size:13px;">{{ session('success') }}</div></div>
        @endif
        @if(session('error'))
        <div class="alert alert-error" style="margin-bottom:16px;"><span class="alert-icon">!</span><div style="font-size:13px;">{{ session('error') }}</div></div>
        @endif

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;margin-bottom:24px;">
            @foreach(['resume' => 'Resume development', 'linkedin' => 'LinkedIn optimisation'] as $key => $label)
            @php $q = $quotes[$key]; @endphp
            <div class="card" style="padding:18px;display:flex;flex-direction:column;gap:10px;">
                <div style="font-size:15px;font-weight:800;">{{ $label }}</div>
                <p style="font-size:12px;color:var(--text-2);line-height:1.45;margin:0;">{{ $q['entitlement']['label'] }}</p>
                <div style="font-size:22px;font-weight:800;color:var(--brand);">
                    @if($q['is_free']) Included @else ₹{{ number_format($q['amount'], 0) }} @endif
                </div>
                <a href="{{ route('mentee.career-services.create', ['type' => $key]) }}" class="btn btn-primary" style="width:100%;margin-top:auto;">
                    Request {{ $key === 'resume' ? 'resume review' : 'LinkedIn review' }}
                </a>
            </div>
            @endforeach
        </div>

        <div class="card" style="padding:0;overflow:hidden;">
            <div style="padding:16px 18px;border-bottom:1px solid var(--border);font-weight:700;font-size:14px;">Your requests</div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Invoice</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                        @php
                            $tone = match ($req->status) {
                                'completed' => 'success',
                                'submitted' => 'warning',
                                'pending_payment' => 'error',
                                default => 'muted',
                            };
                        @endphp
                        <tr>
                            <td style="font-weight:600;">{{ $req->typeLabel() }}</td>
                            <td><span class="cs-status cs-status--{{ $tone }}">{{ $req->statusLabel() }}</span></td>
                            <td>{{ $req->created_at?->format('d M Y') }}</td>
                            <td>
                                @if($req->invoice)
                                    <a href="{{ route('mentee.career-service-invoices.download', $req->invoice) }}" class="btn btn-ghost btn-sm">{{ $req->invoice->invoice_number }}</a>
                                @else
                                    <span style="color:var(--text-3);font-size:12px;">—</span>
                                @endif
                            </td>
                            <td><a href="{{ route('mentee.career-services.show', $req) }}" class="btn btn-ghost btn-sm">View</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--text-3);padding:28px;">No requests yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($requests->hasPages())
            <div style="padding:12px 18px;">@include('frontend.partials.pagination', ['paginator' => $requests])</div>
            @endif
        </div>
    </div>
</div>
@endsection
