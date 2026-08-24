@extends('frontend.layouts.app')
@section('title', $assessment->title.' — Assessment')

@section('content')
<div class="dash-layout assess-page">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div>
                <a href="{{ route('mentor.assessments.index') }}" class="assess-back">← Back to Assessments</a>
                <div class="dash-title">{{ $assessment->title }}</div>
            </div>
            <a href="{{ route('mentor.assessments.edit', $assessment) }}" class="btn btn-outline btn-sm">Edit</a>
        </div>

        <div class="assess-form-card" style="margin-bottom:16px;">
            <div class="assess-show-head">
                @if($assessment->iconUrl())
                    <img src="{{ $assessment->iconUrl() }}" class="assess-icon-lg" alt="">
                @endif
                <div class="grow">
                    <div class="dash-title" style="margin-bottom:6px;">{{ $assessment->title }}</div>
                    @if($assessment->isActive())
                    <span class="assess-badge is-active">Active</span>
                    @else
                    <span class="assess-badge is-inactive">Inactive</span>
                    @endif
                    @if($assessment->description)
                    <p class="dash-subtitle" style="margin-top:10px;white-space:pre-wrap;">{{ $assessment->description }}</p>
                    @endif
                </div>
                @if($assessment->imageUrl())
                    <img src="{{ $assessment->imageUrl() }}" class="assess-cover" alt="">
                @endif
            </div>
            @if($assessment->instructions)
            <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
                <div class="assess-section-title">Instructions</div>
                <p class="dash-subtitle" style="white-space:pre-wrap;">{{ $assessment->instructions }}</p>
            </div>
            @endif
        </div>

        <div class="assess-band-grid">
            @forelse($assessment->scoreBands as $band)
            <div class="assess-form-card">
                <div style="font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--brand);margin-bottom:6px;">Score Band {{ $band->band_index }}</div>
                <div style="font-weight:700;">{{ $band->heading ?: 'Untitled' }}</div>
                <div class="dash-subtitle" style="margin-top:4px;">From {{ $band->range_from }} to {{ $band->range_to }}</div>
                @if($band->description)
                <div class="dash-subtitle" style="margin-top:12px;">{!! $band->description !!}</div>
                @endif
            </div>
            @empty
            <p class="dash-subtitle">No score bands yet.</p>
            @endforelse
        </div>

        <div class="assess-form-card">
            <div class="assess-section-title">Categories & Questions</div>
            @forelse($assessment->categories as $category)
            <div style="margin-bottom:18px;">
                <div style="font-size:14px;font-weight:700;margin-bottom:6px;">{{ $category->name }}</div>
                @forelse($category->questions as $q)
                <div class="assess-question">{{ $q->question }}</div>
                @empty
                <p class="dash-subtitle">No questions in this category.</p>
                @endforelse
            </div>
            @empty
                @forelse($questions ?? [] as $idx => $q)
                @php
                    $text = is_object($q) ? ($q->question ?? '') : (is_array($q) ? ($q['question'] ?? 'Question') : (string) $q);
                    $options = is_object($q) ? ($q->optionLabels() ?? []) : (is_array($q) ? ($q['options'] ?? []) : []);
                @endphp
                <div class="assess-question">
                    <strong>{{ $idx + 1 }}. {{ $text }}</strong>
                    @foreach($options as $optIdx => $option)
                    <div>{{ $optIdx }}. {{ is_array($option) ? ($option['text'] ?? json_encode($option)) : $option }}</div>
                    @endforeach
                </div>
                @empty
                <p class="dash-subtitle">No categories yet. Create a category, then add questions.</p>
                @endforelse
            @endforelse
        </div>

        @if(($completions ?? collect())->isNotEmpty())
        <div class="assess-form-card" style="margin-top:16px;">
            <div class="assess-section-title">Recent Submissions</div>
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
