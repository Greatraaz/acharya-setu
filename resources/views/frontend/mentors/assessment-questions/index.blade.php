@extends('frontend.layouts.app')
@section('title', 'Assessment Questions — Vedrix Mentor')

@section('content')
<div class="dash-layout assess-page">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header dash-header--actions">
            <div class="dash-header__main">
                <div class="dash-title">Assessment Questions</div>
                <div class="dash-subtitle">Questions use a fixed 0–3 scale. Option labels can be edited per question.</div>
            </div>
            <div class="dash-header__actions">
                <a href="{{ route('mentor.assessment-questions.create') }}" class="btn btn-primary btn-sm">+ Add New</a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('mentor.assessment-questions.index') }}" class="assess-questions-toolbar">
            <div class="assess-questions-toolbar__grid">
                <div class="assess-questions-toolbar__field assess-questions-toolbar__field--question">
                    <label class="assess-questions-toolbar__label" for="assess-question-search">Question</label>
                    <div class="assess-questions-toolbar__search-row">
                        <div class="session-search-field assess-questions-toolbar__search">
                            <span class="session-search-icon" aria-hidden="true">🔍</span>
                            <input id="assess-question-search" type="search" name="search" value="{{ request('search') }}"
                                   placeholder="Question text…" class="form-input" autocomplete="off">
                        </div>
                        <button type="submit" class="btn btn-outline assess-questions-toolbar__submit assess-questions-toolbar__submit--inline">Search</button>
                    </div>
                </div>
                <div class="assess-questions-toolbar__field assess-questions-toolbar__field--assessment">
                    <label class="assess-questions-toolbar__label" for="assess-question-filter">Assessment</label>
                    <select id="assess-question-filter" name="assessment_id" class="form-input form-select" aria-label="Filter by assessment">
                        <option value="">All assessments</option>
                        @foreach($assessments as $a)
                        <option value="{{ $a->id }}" @selected((string) request('assessment_id') === (string) $a->id)>{{ $a->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="assess-questions-toolbar__actions">
                    <button type="submit" class="btn btn-outline assess-questions-toolbar__submit assess-questions-toolbar__submit--bar">Search</button>
                    @if(request()->filled('search') || request()->filled('assessment_id'))
                    <a href="{{ route('mentor.assessment-questions.index') }}" class="btn btn-ghost">Clear</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="assess-table-wrap">
            <table class="assess-table assess-table--questions">
                <thead>
                    <tr>
                        <th>Question</th>
                        <th>Assessment</th>
                        <th class="actions">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($questions as $question)
                    <tr>
                        <td class="assess-title-cell" data-label="Question">{{ $question->question }}</td>
                        <td data-label="Assessment"><span class="assess-meta-pill">{{ $question->assessment->title ?? '—' }}</span></td>
                        <td class="actions" data-label="Action">
                            <div class="assess-actions">
                                <a href="{{ route('mentor.assessment-questions.edit', $question) }}"
                                   class="assess-icon-btn assess-icon-btn--edit" title="Edit">✎</a>
                                <form method="POST" action="{{ route('mentor.assessment-questions.destroy', $question) }}"
                                      onsubmit="return confirm('Delete this question?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="assess-icon-btn assess-icon-btn--delete" title="Delete">🗑</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3">
                            <div class="assess-empty">
                                <div style="font-size:40px;">❓</div>
                                <h3>No questions yet</h3>
                                <p>Create an assessment first, then add questions.</p>
                                <a href="{{ route('mentor.assessment-questions.create') }}" class="btn btn-primary" style="margin-top:14px;">Add First Question</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($questions->hasPages())
            <div style="padding:14px 18px;border-top:1px solid var(--border);">
            @include('frontend.partials.pagination', ['paginator' => $questions])
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
