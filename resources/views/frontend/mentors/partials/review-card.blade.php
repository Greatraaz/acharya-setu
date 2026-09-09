@php
    $optionText = $optionText ?? function ($option) {
        if (is_array($option)) {
            return (string) ($option['text'] ?? json_encode($option));
        }
        return (string) $option;
    };
    $progress = $row['progress'];
    $context = $row['context'] ?? [];
    $mentee = $progress->user;
    $showMentee = $showMentee ?? true;
@endphp
<div class="card mentor-review-card" id="review-{{ $progress->id }}">
    <div class="mentor-review-card__head">
        <div>
            <div class="mentor-review-card__eyebrow">
                {{ strtoupper($progress->item_type) }}
                @if(!empty($context['track_name'])) · {{ $context['track_name'] }}@endif
                @if(!empty($context['month_number'])) · Month {{ $context['month_number'] }}@endif
                @if(!empty($context['week_number'])) · Week {{ $context['week_number'] }}@endif
            </div>
            <div class="mentor-review-card__title">{{ $context['title'] ?? ('Item #'.$progress->item_id) }}</div>
            @if($showMentee)
            <div class="mentor-review-card__meta">
                Mentee: <strong>{{ $mentee->name ?? '—' }}</strong>
                @if($mentee)
                    · <a href="{{ route('mentor.journey.show', $mentee->id) }}" style="color:var(--brand);">Open progress</a>
                @endif
            </div>
            @endif
        </div>
        <span class="mentor-review-badge">Needs review</span>
    </div>

    @if($progress->item_type === 'mcq')
        <div class="mentor-mcq-options">
            @foreach(($context['options'] ?? []) as $idx => $option)
            @php
                $isSelected = isset($context['selected_index']) && (int) $context['selected_index'] === (int) $idx;
                $isCorrect = isset($context['correct_index']) && (int) $context['correct_index'] === (int) $idx;
            @endphp
            <div class="mentor-mcq-option {{ $isSelected ? 'is-selected' : '' }} {{ $isCorrect ? 'is-correct' : '' }}">
                <strong>{{ chr(65 + (int) $idx) }}.</strong> {{ $optionText($option) }}
                @if($isSelected)<span class="mentor-mcq-tag">Mentee answer</span>@endif
                @if($isCorrect)<span class="mentor-mcq-tag mentor-mcq-tag--ok">Correct</span>@endif
            </div>
            @endforeach
        </div>
    @else
        @if($progress->submission_text)
        <div class="mentor-review-card__body">{{ $progress->submission_text }}</div>
        @endif
        @if($progress->submission_url)
        <div style="margin-bottom:12px;">
            <a href="{{ $progress->submissionLink() }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">Open submission</a>
        </div>
        @endif
        @if(! $progress->submission_text && ! $progress->submission_url)
        <p class="mentor-review-card__hint">No written submission attached.</p>
        @endif
    @endif

    <form method="POST" action="{{ route('mentor.submissions.review', $progress->id) }}" class="mentor-review-card__form">
        @csrf
        <label class="form-label" for="feedback-{{ $progress->id }}">Feedback to mentee (optional)</label>
        <textarea id="feedback-{{ $progress->id }}" name="mentor_feedback" class="form-input" rows="2" placeholder="e.g. Solid reasoning — tighten the conclusion."></textarea>
        <div class="mentor-review-card__actions">
            <button type="submit" name="submission_status" value="approved" class="btn btn-primary btn-sm">Approve</button>
            <button type="submit" name="submission_status" value="rejected" class="btn btn-outline btn-sm" style="color:var(--error);">Request changes</button>
        </div>
    </form>
</div>
