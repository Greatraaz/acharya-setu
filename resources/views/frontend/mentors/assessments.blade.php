@extends('frontend.layouts.app')
@section('title', 'Assessments — Vedrix Mentor')

@section('content')
<div class="dash-layout assess-page">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header dash-header--actions">
            <div class="dash-header__main">
                <div class="dash-title">Assessments</div>
                <div class="dash-subtitle">Manage assessment categories with score bands and status{{ isset($menteeCount) ? ' · '.$menteeCount.' mentee'.($menteeCount === 1 ? '' : 's') : '' }}.</div>
            </div>
            @unless($tableMissing ?? false)
            <div class="dash-header__actions">
                <a href="{{ route('mentor.assessments.create') }}" class="btn btn-primary btn-sm">+ Add New</a>
            </div>
            @endunless
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
        @endif

        @if($tableMissing ?? false)
        <div class="alert alert-error">Assessments are not available yet. Ask admin to run database migrations.</div>
        @else
        <form method="GET" action="{{ route('mentor.assessments.index') }}" class="session-toolbar assess-toolbar">
            <div class="session-filter-tabs">
                @foreach(['' => 'All', 'active' => 'Active', 'inactive' => 'Inactive'] as $key => $label)
                    @php $tabParams = array_filter(['status' => $key ?: null, 'search' => ($search ?? request('search')) ?: null]); @endphp
                    <a href="{{ route('mentor.assessments.index', $tabParams) }}"
                       class="session-filter-tab {{ ($status ?? request('status', '')) === $key ? 'active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <div class="assess-toolbar__grid">
                @if(($status ?? request('status')))
                    <input type="hidden" name="status" value="{{ $status ?? request('status') }}">
                @endif
                <div class="assess-toolbar__search session-search-field">
                    <span class="session-search-icon" aria-hidden="true">🔍</span>
                    <input type="search" name="search" class="form-input" value="{{ $search ?? request('search') }}"
                           placeholder="Search assessments…" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-outline assess-toolbar__submit">Search</button>
            </div>
        </form>

        <div class="assess-table-wrap">
            <table class="assess-table assess-table--assessments">
                <thead>
                    <tr>
                        <th class="num">Sr. No.</th>
                        <th>Image</th>
                        <th>Assessment</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th class="actions">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assessments as $index => $assessment)
                    <tr>
                        <td class="num" data-label="Sr. No.">{{ $assessments->firstItem() + $index }}</td>
                        <td data-label="Image">
                            @if($assessment->imageUrl())
                                <img src="{{ $assessment->imageUrl() }}" class="assess-thumb" alt="">
                            @else
                                <div class="assess-thumb">📝</div>
                            @endif
                        </td>
                        <td class="assess-title-cell" data-label="Assessment">{{ $assessment->title }}</td>
                        <td data-label="Description">
                            <div class="assess-desc-cell">{{ strip_tags($assessment->description ?? '—') }}</div>
                        </td>
                        <td data-label="Status">
                            @if($assessment->isActive())
                            <span class="assess-badge is-active">Active</span>
                            @else
                            <span class="assess-badge is-inactive">Inactive</span>
                            @endif
                        </td>
                        <td class="actions" data-label="Action">
                            <div class="assess-actions">
                                <a href="{{ route('mentor.assessments.show', $assessment) }}" class="assess-icon-btn assess-icon-btn--view" title="View">👁</a>
                                <a href="{{ route('mentor.assessments.edit', $assessment) }}" class="assess-icon-btn assess-icon-btn--edit" title="Edit">✎</a>
                                <form method="POST" action="{{ route('mentor.assessments.destroy', $assessment) }}"
                                      onsubmit="return confirm('Delete this assessment and all its questions?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="assess-icon-btn assess-icon-btn--delete" title="Delete">🗑</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="assess-empty">
                                <div style="font-size:40px;">📝</div>
                                <h3>No assessments yet</h3>
                                <p>Add an assessment, then add questions for your mentees.</p>
                                <a href="{{ route('mentor.assessments.create') }}" class="btn btn-primary" style="margin-top:14px;">Create Assessment</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('frontend.partials.pagination', ['paginator' => $assessments])
        @endif
    </div>
</div>
@endsection
