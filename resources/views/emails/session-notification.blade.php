@extends('emails.layout')

@section('email_title', $headline.' — Vedrix')

@section('content')
    <div class="badge">
        @if($event === 'booked')
            Session booked
        @elseif($event === 'cancelled')
            Session cancelled
        @else
            Session completed
        @endif
    </div>

    <div class="greeting">{{ $headline }}</div>
    <p class="desc">{{ $intro }}</p>

    <div class="details">
        @foreach($sessionDetails as $label => $value)
        <div class="details-row">
            <span class="details-label">{{ $label }}</span>
            <span class="details-value">{{ $value }}</span>
        </div>
        @endforeach
    </div>

    @if(!empty($footerNote))
    <div class="note">{{ $footerNote }}</div>
    @endif

    @if(!empty($cta['url'] ?? null))
    <div class="btn-wrap">
        <a href="{{ $cta['url'] }}" class="btn">{{ $cta['label'] ?? 'View session' }}</a>
    </div>
    @endif

    <p class="signoff">
        — The Vedrix Team
    </p>
@endsection
