@extends('frontend.layouts.app')
@section('title', 'Mock Interview #'.$item->id.' — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content" style="max-width:640px;">
        <a href="{{ route('mentor.mock-interviews.index') }}" style="font-size:13px;color:var(--brand);">← Mock Interviews</a>
        <div class="dash-title" style="margin-top:10px;">Mock Interview #{{ $item->id }}</div>
        <div class="dash-subtitle">{{ $item->statusLabel() }}</div>

        @if(session('error'))
        <div class="alert alert-error" style="margin:16px 0;"><span class="alert-icon">!</span><div style="font-size:13px;">{{ session('error') }}</div></div>
        @endif

        <div class="card" style="padding:20px;margin-top:16px;display:grid;gap:12px;">
            <div><strong>Mentee:</strong> {{ $item->user->name ?? '—' }}</div>
            <div><strong>Preferred:</strong> {{ $item->preferred_at?->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</div>
            <div><strong>Duration:</strong> {{ $item->duration_minutes }} minutes</div>
            @if($item->target_role)
            <div><strong>Target role:</strong> {{ $item->target_role }}</div>
            @endif
            @if($item->mentee_notes)
            <div><strong>Notes:</strong> {{ $item->mentee_notes }}</div>
            @endif
            @if($item->admin_notes)
            <div><strong>Admin notes:</strong> {{ $item->admin_notes }}</div>
            @endif

            @if($item->canJoinCall())
                <a href="{{ route('mock-interviews.call', $item->id) }}" class="btn btn-primary btn-lg" style="margin-top:8px;">🎥 Join Agora call</a>
            @elseif($item->status === 'confirmed')
                <p style="font-size:13px;color:var(--text-2);margin:8px 0 0;">Call opens 15 minutes before the preferred time and closes when the duration ends.</p>
            @endif
        </div>
    </div>
</div>
@endsection
