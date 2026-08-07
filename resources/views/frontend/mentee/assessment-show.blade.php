@extends('frontend.layouts.app')
@section('title', $assessment->title.' — Assessment')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div style="font-size:12px;margin-bottom:12px;">
            <a href="{{ route('mentee.assessments.index') }}" style="color:var(--brand);">← Assessments</a>
        </div>

        <div class="dash-header">
            <div style="font-size:11px;color:var(--brand);font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Month {{ $assessment->month }}</div>
            <div class="dash-title">{{ $assessment->title }}</div>
            <div class="dash-subtitle">{{ $assessment->description ?: 'Answer all questions and submit when ready.' }}</div>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
        @endif

        @if($progress?->completed_at)
        <div class="alert alert-info" style="margin-bottom:16px;">
            <span class="alert-icon">✅</span>
            <div style="font-size:13px;">
                Last score: <strong>{{ number_format((float) $progress->score, 0) }}%</strong>
                on {{ $progress->completed_at->format('d M Y') }}. You can retake below.
            </div>
        </div>
        @endif

        @if($questions->isEmpty())
        <div class="empty-state" style="padding:40px 0;">
            <div style="font-size:16px;font-weight:700;">No questions in this assessment</div>
        </div>
        @else
        <form id="assessment-form" method="POST" action="{{ route('mentee.assessments.submit', $assessment->id) }}">
            @csrf
            @foreach($questions as $idx => $q)
            @php
                $text = is_array($q) ? ($q['question'] ?? $q['text'] ?? 'Question '.($idx + 1)) : (string) $q;
                $options = is_array($q) ? ($q['options'] ?? []) : [];
                $prev = is_array($progress?->answers ?? null) ? ($progress->answers[$idx] ?? null) : null;
            @endphp
            <div class="card" style="margin-bottom:14px;padding:16px 18px;">
                <div style="font-size:14px;font-weight:700;margin-bottom:12px;">
                    {{ $idx + 1 }}. {{ $text }}
                </div>
                <div style="display:grid;gap:8px;">
                    @foreach($options as $optIdx => $option)
                    <label style="display:flex;gap:10px;align-items:flex-start;padding:10px 12px;border:1px solid var(--border);border-radius:var(--radius);cursor:pointer;">
                        <input type="radio" name="answers[{{ $idx }}]" value="{{ $optIdx }}"
                               @checked((string) $prev === (string) $optIdx) required
                               style="margin-top:3px;accent-color:var(--brand);">
                        <span style="font-size:13px;color:var(--text-2);">
                            {{ chr(65 + (int) $optIdx) }}. {{ is_array($option) ? ($option['text'] ?? json_encode($option)) : $option }}
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach

            <button type="submit" class="btn btn-primary btn-lg" id="assessment-submit-btn">
                Submit Assessment →
            </button>
        </form>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('assessment-form')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const form = this;
    const fd = new FormData(form);
    const answers = {};
    fd.forEach((val, key) => {
        const m = key.match(/^answers\[(\d+)\]$/);
        if (m) answers[m[1]] = parseInt(val, 10);
    });

    AjaxPost(form.action, { answers }, {
        btn: document.getElementById('assessment-submit-btn'),
        loader: true,
        onSuccess: (data) => {
            const score = data.result?.score ?? '';
            showToast('success', data.message || `Submitted! Score: ${score}%`);
            setTimeout(() => location.href = data.redirect || '{{ route('mentee.assessments.show', $assessment->id) }}', 900);
        },
        onError: (err) => showToast('error', err.message || 'Could not submit assessment.'),
    });
});
</script>
@endpush
