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
    $typeLabel = match ($progress->item_type) {
        'mcq' => 'MCQ',
        'task' => 'Task',
        default => strtoupper((string) $progress->item_type),
    };
@endphp
<div class="card mentor-review-card" id="review-{{ $progress->id }}">
    <div class="mentor-review-card__head">
        <div class="mentor-review-card__person">
            @if($showMentee)
            <div class="mentor-avatar-lg mentor-review-card__avatar">
                @if($mentee?->avatar_url)
                    <img src="{{ $mentee->avatar_url }}" alt="">
                @else
                    {{ strtoupper(substr($mentee?->name ?? 'M', 0, 1)) }}
                @endif
            </div>
            @endif
            <div class="mentor-review-card__copy">
                <div class="mentor-review-card__chips">
                    <span class="mentor-review-chip">{{ $typeLabel }}</span>
                    @if(!empty($context['track_name']))
                        <span class="mentor-review-chip">{{ $context['track_name'] }}</span>
                    @endif
                    @if(!empty($context['month_number']))
                        <span class="mentor-review-chip">Month {{ $context['month_number'] }}</span>
                    @endif
                    @if(!empty($context['week_number']))
                        <span class="mentor-review-chip">Week {{ $context['week_number'] }}</span>
                    @endif
                </div>
                <div class="mentor-review-card__title">{{ $context['title'] ?? ('Item #'.$progress->item_id) }}</div>
                @if($showMentee)
                <div class="mentor-review-card__meta">
                    {{ $mentee->name ?? 'Mentee' }}
                    @if($mentee?->email) · {{ $mentee->email }}@endif
                    @if($progress->updated_at) · Submitted {{ $progress->updated_at->diffForHumans() }}@endif
                    @if($mentee)
                        · <a href="{{ route('mentor.journey.show', $mentee->id) }}">Open journey</a>
                    @endif
                </div>
                @endif
            </div>
        </div>
        <span class="mentor-review-badge">Needs review</span>
    </div>

    <div class="mentor-review-card__section">
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
            <div style="margin-top:{{ $progress->submission_text ? '10px' : '0' }};">
                <a href="{{ $progress->submissionLink() }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">Open submission</a>
            </div>
            @endif
            @if(! $progress->submission_text && ! $progress->submission_url)
            <div class="mentor-review-card__empty">No written submission attached. You can still approve or request changes.</div>
            @endif
        @endif
    </div>

    <form method="POST" action="{{ route('mentor.submissions.review', $progress->id) }}" class="mentor-review-card__form">
        @csrf
        <label class="form-label" for="feedback-{{ $progress->id }}">Feedback to mentee <span style="font-weight:500;text-transform:none;letter-spacing:0;">(optional)</span></label>
        <textarea id="feedback-{{ $progress->id }}" name="mentor_feedback" class="form-input" rows="3" placeholder="e.g. Solid reasoning — tighten the conclusion."></textarea>
        <div class="mentor-review-card__actions">
            <button type="submit" name="submission_status" value="approved" class="btn btn-success btn-sm">Approve</button>
            <button type="submit" name="submission_status" value="rejected" class="btn btn-outline btn-sm" style="color:var(--error);border-color:color-mix(in srgb, var(--error) 45%, var(--border));">Request changes</button>
        </div>
    </form>
</div>
