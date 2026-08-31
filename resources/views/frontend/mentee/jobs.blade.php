@extends('frontend.layouts.app')
@section('title', 'Job Listings — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">Job Listings</div>
            <div class="dash-subtitle">Open roles curated for mentees on Vedrix.</div>
        </div>

        <form method="GET" action="{{ route('mentee.jobs') }}" class="card" style="margin-bottom:18px;padding:14px 16px;">
            <div style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:10px;align-items:end;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-input" value="{{ request('search') }}" placeholder="Title, department, location">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Type</label>
                    <select name="job_type" class="form-input form-select">
                        <option value="">All</option>
                        @foreach(\App\Models\JobListing::JOB_TYPES as $key => $label)
                        <option value="{{ $key }}" @selected(request('job_type') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Location</label>
                    <select name="location_type" class="form-input form-select">
                        <option value="">All</option>
                        @foreach(\App\Models\JobListing::LOCATION_TYPES as $key => $label)
                        <option value="{{ $key }}" @selected(request('location_type') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>

        @forelse($jobs as $job)
        <a href="{{ route('mentee.jobs.show', $job->id) }}" class="card" style="display:block;text-decoration:none;color:inherit;margin-bottom:12px;padding:16px 18px;">
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                <div>
                    <div style="font-size:15px;font-weight:700;margin-bottom:4px;">{{ $job->title }}</div>
                    <div style="font-size:12px;color:var(--text-2);">
                        {{ $job->department ?: 'General' }}
                        · {{ $job->location }}
                        · {{ $job->location_type_label }}
                    </div>
                </div>
                <div style="text-align:right;">
                    <span class="tag">{{ $job->job_type_label }}</span>
                    <div style="font-size:12px;color:var(--brand);font-weight:600;margin-top:6px;">{{ $job->salary_range }}</div>
                </div>
            </div>
        </a>
        @empty
        <div class="empty-state" style="padding:60px 0;">
            <div style="font-size:48px;margin-bottom:12px;">💼</div>
            <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No open roles right now</div>
            <p style="font-size:13px;color:var(--text-2);">Check back later for new opportunities.</p>
        </div>
        @endforelse

        @include('frontend.partials.pagination', ['paginator' => $jobs])
    </div>
</div>
@endsection
