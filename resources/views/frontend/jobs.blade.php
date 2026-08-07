@extends('frontend.layouts.app')
@section('title', 'Jobs — Vedrix')

@section('content')
<div class="container" style="padding:calc(var(--nav-h) + 32px) 20px 60px;max-width:900px;margin:0 auto;">
    <h1 style="font-size:28px;font-weight:800;margin-bottom:8px;">Job Listings</h1>
    <p style="color:var(--text-2);margin-bottom:24px;">Open roles from the Vedrix community.</p>

    @forelse($jobs as $job)
    <a href="{{ route('jobs.public.show', $job->id) }}" class="card" style="display:block;text-decoration:none;color:inherit;margin-bottom:12px;padding:16px 18px;">
        <div style="font-size:15px;font-weight:700;">{{ $job->title }}</div>
        <div style="font-size:12px;color:var(--text-2);margin-top:4px;">
            {{ $job->location }} · {{ $job->job_type_label ?? $job->job_type }} · {{ $job->salary_range }}
        </div>
    </a>
    @empty
    <div class="empty-state" style="padding:48px 0;">
        <div style="font-size:16px;font-weight:700;">No open roles right now</div>
    </div>
    @endforelse

    @if($jobs->hasPages())
    <div style="margin-top:20px;display:flex;justify-content:center;">{{ $jobs->links() }}</div>
    @endif
</div>
@endsection
