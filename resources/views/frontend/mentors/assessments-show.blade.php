@extends('frontend.layouts.app')
@section('title', $assessment->title.' — Assessment')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div style="font-size:12px;margin-bottom:12px;">
            <a href="{{ route('mentor.assessments.index') }}" style="color:var(--brand);">← Assessments</a>
        </div>

        <div class="dash-header" style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
            <div>
                <div class="dash-title">{{ $assessment->title }}</div>
                @if($assessment->description)
                <div class="dash-subtitle">{{ $assessment->description }}</div>
                @endif
            </div>
            <a href="{{ route('mentor.assessments.edit', $assessment) }}" class="btn btn-primary btn-sm">Edit</a>
        </div>

        <div class="card" style="margin-bottom:16px;padding:16px 18px;">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;">Questions ({{ $questions->count() }})</h3>
            @forelse($questions as $idx => $q)
            @php
                $text = is_object($q) ? ($q->question ?? '') : (is_array($q) ? ($q['question'] ?? 'Question') : (string) $q);
                $options = is_object($q) ? ($q->optionLabels() ?? []) : (is_array($q) ? ($q['options'] ?? []) : []);
            @endphp
            <div style="padding:12px 0;border-bottom:1px solid var(--border);">
                <div style="font-size:14px;font-weight:700;margin-bottom:8px;">{{ $idx + 1 }}. {{ $text }}</div>
                @foreach($options as $optIdx => $option)
                <div style="font-size:13px;color:var(--text-2);padding:4px 0;">
                    {{ $optIdx }}. {{ is_array($option) ? ($option['text'] ?? json_encode($option)) : $option }}
                </div>
                @endforeach
            </div>
            @empty
            <p style="font-size:13px;color:var(--text-2);margin:0;">No questions yet.</p>
            @endforelse
        </div>

        @if($completions->isNotEmpty())
        <div class="card" style="padding:16px 18px;">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;">Recent Submissions</h3>
            @foreach($completions as $row)
            <div style="display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);font-size:13px;">
                <div>
                    <div style="font-weight:700;">{{ $row->user->name ?? 'Mentee #'.$row->user_id }}</div>
                    <div style="color:var(--text-3);font-size:12px;">{{ $row->completed_at?->format('d M Y, g:i A') }}</div>
                </div>
                <div style="font-weight:700;color:var(--brand);">{{ number_format((float) $row->score, 0) }}%</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
