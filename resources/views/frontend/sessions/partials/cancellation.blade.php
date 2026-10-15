@php $cancelInfo = $session->cancellationInfo(); @endphp
@if($cancelInfo)
<div class="card session-detail-card" style="border:1px solid rgba(239,68,68,.35);">
    <h3 style="font-size:14px;font-weight:700;margin-bottom:8px;">Session cancelled</h3>
    <p style="font-size:13px;color:var(--text-2);margin:0 0 6px;">
        Cancelled by <strong>{{ $cancelInfo['actor_name'] }}</strong> ({{ $cancelInfo['role_label'] }})
        @if($cancelInfo['when'])
            on {{ $cancelInfo['when'] }}
        @endif
    </p>
    <p style="font-size:13px;color:var(--text-2);margin:0 0 10px;">Reason: {{ $cancelInfo['reason'] }}</p>
    @foreach($cancelInfo['lines'] as $line)
        <p style="font-size:13px;margin:0 0 4px;">{{ $line }}</p>
    @endforeach
    @if($cancelInfo['wallet_reference'])
        <p style="font-size:12px;color:var(--text-3);margin:8px 0 0;">Transaction {{ $cancelInfo['wallet_reference'] }}</p>
    @endif
    @if($cancelInfo['wallet_refunded'] > 0 && auth()->user()?->role === 'mentee')
        <a href="{{ route('mentee.wallet') }}" class="btn btn-outline btn-sm" style="margin-top:12px;">View refund in wallet</a>
    @endif
</div>
@endif
