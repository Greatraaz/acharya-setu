@extends('frontend.layouts.app')
@section('title', $job->title.' — Jobs')

@section('content')
<div class="container" style="padding:calc(var(--nav-h) + 32px) 20px 60px;max-width:800px;margin:0 auto;">
    <a href="{{ route('jobs.public') }}" style="color:var(--brand);font-size:13px;">← All jobs</a>
    <h1 style="font-size:28px;font-weight:800;margin:12px 0 8px;">{{ $job->title }}</h1>
    <p style="color:var(--text-2);margin-bottom:20px;">{{ $job->location }} · {{ $job->job_type_label ?? $job->job_type }} · {{ $job->salary_range }}</p>
    <div class="card" style="padding:20px;white-space:pre-wrap;font-size:14px;line-height:1.7;color:var(--text-2);">{{ $job->description }}</div>
    @if($job->apply_url)
    <a href="{{ $job->apply_url }}" target="_blank" class="btn btn-primary" style="margin-top:16px;">Apply Now →</a>
    @elseif($job->apply_email)
    <a href="mailto:{{ $job->apply_email }}" class="btn btn-primary" style="margin-top:16px;">Email to Apply</a>
    @endif
</div>
@endsection
