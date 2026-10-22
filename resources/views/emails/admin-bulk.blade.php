@extends('emails.layout')

@section('email_title', $headline.' — Vedrix')

@section('content')
    <div class="badge">Message from Vedrix</div>
    <div class="greeting">Hi {{ \Illuminate\Support\Str::before(trim((string) ($recipient->name ?: 'there')), ' ') }},</div>
    <div class="desc" style="white-space:normal;color:#d4d4d8;">{!! $bodyHtml !!}</div>
    <p class="signoff">
        — The Vedrix Team
    </p>
@endsection
