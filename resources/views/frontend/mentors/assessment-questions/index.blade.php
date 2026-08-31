@extends('frontend.layouts.app')
@section('title', 'Assessment Questions — Vedrix Mentor')

@section('content')
<div class="dash-layout assess-page">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div>
                <div class="dash-title">Assessment Questions</div>
                <div class="dash-subtitle">Questions use a fixed 0–3 scale. Option labels can be edited per question.</div>
            </div>
            <a href="{{ route('mentor.assessment-questions.create') }}" class="btn btn-primary btn-sm">+ Add New</a>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
        @endif

        {{-- Filter bar --}}
        <form method="GET" class="assess-form-card" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:12px;margin-bottom:20px;padding:16px 18px;">
            <div style="flex:1;min-width:180px;">
                <label class="form-label" style="margin-bottom:6px;">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Question text…" class="form-input">
            </div>
            <div>
                <label class="form-label" style="margin-bottom:6px;">Assessment</label>
                <select name="assessment_id" class="form-select" style="min-width:180px;">
                    <option value="">All</option>
                    @foreach($assessments as $a)
                    <option value="{{ $a->id }}" @selected((string) request('assessment_id') === (string) $a->id)>{{ $a->title }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:8px;align-items:flex-end;">
                <button class="btn btn-primary btn-sm" type="submit">Filter</button>
                <a href="{{ route('mentor.assessment-questions.index') }}" class="btn btn-outline btn-sm">Reset</a>
            </div>
        </form>

        <div class="assess-table-wrap">
            <table class="assess-table">
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
                        <td data-label="Assessment" style="color:var(--text-2);">{{ $question->assessment->title ?? '—' }}</td>
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
