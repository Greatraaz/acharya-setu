@extends('frontend.layouts.app')
@section('title', 'Mock Interviews — Vedrix')

@php
    $rate = (float) ($quote['rate_per_minute'] ?? 0);
@endphp

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header flex-between" style="gap:12px;flex-wrap:wrap;">
            <div>
                <div class="dash-title">Mock Interviews</div>
                <div class="dash-subtitle">Practice interviews with Vedrix — book a slot that works for you.</div>
            </div>
        </div>

        @if(session('success') || request('submitted'))
        <div class="alert alert-success" style="margin-bottom:16px;"><span class="alert-icon">✓</span><div style="font-size:13px;">{{ session('success') ?? 'Payment successful. Your mock interview request is under review.' }}</div></div>
        @endif
        @if(session('error'))
        <div class="alert alert-error" style="margin-bottom:16px;"><span class="alert-icon">!</span><div style="font-size:13px;">{{ session('error') }}</div></div>
        @endif

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-bottom:24px;">
            <div class="card" style="padding:18px;display:flex;flex-direction:column;gap:10px;">
                <div style="font-size:15px;font-weight:800;">Book a mock interview</div>
                <p style="font-size:12px;color:var(--text-2);line-height:1.45;margin:0;">{{ $quote['entitlement']['label'] ?? 'Paid add-on per minute' }}</p>
                <div style="font-size:22px;font-weight:800;color:var(--brand);">
                    @if($quote['is_free'] ?? false)
                        Included
                    @else
                        ₹{{ number_format($rate, 0) }}/min
                    @endif
                </div>
                <a href="{{ route('mentee.mock-interviews.create') }}" class="btn btn-primary" style="width:100%;margin-top:auto;">
                    Request mock interview
                </a>
            </div>
        </div>

        <div class="card" style="padding:0;overflow:hidden;">
            <div style="padding:16px 18px;border-bottom:1px solid var(--border);font-weight:700;font-size:14px;">Your bookings</div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Preferred time</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                        @php
                            $tone = match ($req->status) {
                                'completed' => 'success',
                                'confirmed' => 'success',
                                'submitted' => 'warning',
                                'cancelled' => 'muted',
                                default => 'muted',
                            };
                        @endphp
                        <tr>
                            <td style="font-weight:600;color:var(--text-2);">#{{ $req->id }}</td>
                            <td>{{ $req->preferred_at?->timezone($req->timezone ?: 'Asia/Kolkata')->format('d M Y, h:i A') }}</td>
                            <td>{{ $req->duration_minutes }} min</td>
                            <td><span class="cs-status cs-status--{{ $tone }}">{{ $req->statusLabel() }}</span></td>
                            <td>
                                @if($req->is_paid_addon)
                                    ₹{{ number_format((float) $req->amount, 0) }}
                                @else
                                    <span style="color:var(--text-3);font-size:12px;">Included</span>
                                @endif
                            </td>
                            <td style="display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap;">
                                @if($req->canJoinCall())
                                    <a href="{{ route('mock-interviews.call', $req->id) }}" class="btn btn-primary btn-sm">Join interview</a>
                                @endif
                                <a href="{{ route('mentee.mock-interviews.show', $req) }}" class="btn btn-ghost btn-sm">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center;color:var(--text-3);padding:28px;">No mock interviews yet.</td></tr>
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
