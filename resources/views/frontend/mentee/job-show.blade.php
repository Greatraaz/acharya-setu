@extends('frontend.layouts.app')
@section('title', $job->title.' — Jobs')

@section('content')
@php
    $user = auth()->user();
    $externalApplyUrl = $job->apply_url;
    // Ignore dead / internal hosting panels stored as apply links
    if ($externalApplyUrl && (
        str_contains($externalApplyUrl, 'bigrssock.com')
        || str_contains($externalApplyUrl, ':2083')
        || str_contains($externalApplyUrl, ':2087')
        || ! filter_var($externalApplyUrl, FILTER_VALIDATE_URL)
    )) {
        $externalApplyUrl = null;
    }
@endphp
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div style="font-size:12px;margin-bottom:12px;">
            <a href="{{ route('mentee.jobs') }}" style="color:var(--brand);">← Job Listings</a>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>
        @endif

        <div class="card" style="padding:22px;">
            <div class="dash-title" style="margin-bottom:6px;">{{ $job->title }}</div>
            <div style="font-size:13px;color:var(--text-2);margin-bottom:16px;">
                {{ $job->department ?: 'General' }} · {{ $job->location }} · {{ $job->location_type_label }}
                · {{ $job->job_type_label }} · {{ $job->experience_label }}
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;">
                <span class="tag">{{ $job->salary_range }}</span>
                @if($job->deadline)
                <span class="tag">Deadline {{ $job->deadline->format('d M Y') }}</span>
                @endif
                @if($job->openings)
                <span class="tag">{{ $job->openings }} opening{{ $job->openings > 1 ? 's' : '' }}</span>
                @endif
            </div>

            <h3 style="font-size:14px;font-weight:700;margin-bottom:8px;">Description</h3>
            <div style="font-size:13px;color:var(--text-2);line-height:1.7;white-space:pre-wrap;margin-bottom:16px;">{{ $job->description }}</div>

            @if($job->responsibilities)
            <h3 style="font-size:14px;font-weight:700;margin-bottom:8px;">Responsibilities</h3>
            <div style="font-size:13px;color:var(--text-2);line-height:1.7;white-space:pre-wrap;margin-bottom:16px;">{{ $job->responsibilities }}</div>
            @endif

            @if($job->requirements)
            <h3 style="font-size:14px;font-weight:700;margin-bottom:8px;">Requirements</h3>
            <div style="font-size:13px;color:var(--text-2);line-height:1.7;white-space:pre-wrap;margin-bottom:16px;">{{ $job->requirements }}</div>
            @endif

            @if($job->benefits)
            <h3 style="font-size:14px;font-weight:700;margin-bottom:8px;">Benefits</h3>
            <div style="font-size:13px;color:var(--text-2);line-height:1.7;white-space:pre-wrap;margin-bottom:16px;">{{ $job->benefits }}</div>
            @endif

            @if(is_array($job->skills) && count($job->skills))
            <h3 style="font-size:14px;font-weight:700;margin-bottom:8px;">Skills</h3>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px;">
                @foreach($job->skills as $skill)
                <span class="tag">{{ $skill }}</span>
                @endforeach
            </div>
            @endif

            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                @if($alreadyApplied)
                    <button type="button" class="btn btn-outline" disabled>✓ Already Applied</button>
                @else
                    <button type="button" class="btn btn-primary" onclick="openModal('apply-modal')">Apply Now →</button>
                @endif
                @if($externalApplyUrl)
                    <a href="{{ $externalApplyUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-ghost btn-sm">Open external link</a>
                @elseif($job->apply_email)
                    <a href="mailto:{{ $job->apply_email }}?subject={{ rawurlencode('Application: '.$job->title) }}" class="btn btn-ghost btn-sm">Email recruiter</a>
                @endif
            </div>
        </div>
    </div>
</div>

@unless($alreadyApplied)
<div class="modal-overlay" id="apply-modal">
    <div class="modal" style="max-width:520px;">
        <div class="modal-header">
            <h3>Apply — {{ $job->title }}</h3>
            <button type="button" class="modal-close" onclick="closeModal('apply-modal')">✕</button>
        </div>
        <form id="job-apply-form" method="POST" action="{{ route('mentee.jobs.apply', $job->id) }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="fullname" class="form-input" value="{{ $user->name }}" required maxlength="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Role applying for *</label>
                    <input type="text" name="jobRole" class="form-input" value="{{ $job->title }}" required maxlength="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Qualification *</label>
                    <input type="text" name="qualification" class="form-input" placeholder="e.g. B.Tech CSE" required maxlength="255">
                </div>
                <div class="form-group">
                    <label class="form-label">Specialization</label>
                    <input type="text" name="specification" class="form-input" placeholder="Optional" maxlength="255">
                </div>
                <div class="form-group">
                    <label class="form-label">Skills</label>
                    <input type="text" name="skills" class="form-input" placeholder="PHP, Laravel, MySQL" maxlength="1000">
                </div>
                <div class="form-group">
                    <label class="form-label">Experience</label>
                    <input type="text" name="experience" class="form-input" placeholder="e.g. 2 years" maxlength="100">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Last / Current Job</label>
                    <input type="text" name="lastJob" class="form-input" placeholder="Optional" maxlength="255">
                </div>
                <div id="apply-form-msg" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('apply-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;" id="apply-submit-btn">Submit Application</button>
            </div>
        </form>
    </div>
</div>
@endunless
@endsection

@unless($alreadyApplied)
@push('scripts')
<script>
document.getElementById('job-apply-form')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const form = this;
    const data = Object.fromEntries(new FormData(form).entries());
    delete data._token;

    AjaxPost(form.action, data, {
        btn: document.getElementById('apply-submit-btn'),
        loader: true,
        onSuccess: (res) => {
            showToast('success', res.message || 'Application submitted!');
            closeModal('apply-modal');
            setTimeout(() => location.href = res.redirect || location.href, 800);
        },
        onError: (err) => {
            const box = document.getElementById('apply-form-msg');
            if (box) box.innerHTML = `<div class="alert alert-error">${err.message || 'Could not submit application.'}</div>`;
            showToast('error', err.message || 'Could not submit application.');
        },
    });
});
</script>
@endpush
@endunless
