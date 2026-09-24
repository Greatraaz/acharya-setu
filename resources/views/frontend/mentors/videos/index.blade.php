@extends('frontend.layouts.app')
@section('title', 'Videos — Vedrix Mentor')

@section('content')
<div class="dash-layout assess-page">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header dash-header--actions">
            <div class="dash-header__main">
                <div class="dash-title">Videos</div>
                <div class="dash-subtitle">Share videos with mentees — not tied to curriculum tasks.</div>
            </div>
            <div class="dash-header__actions">
                <a href="{{ route('mentor.videos.create') }}" class="btn btn-primary btn-sm">+ Upload videos</a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('mentor.videos.index') }}" class="session-toolbar assess-toolbar">
            <div class="session-filter-tabs">
                @foreach(['' => 'All', 'active' => 'Active', 'inactive' => 'Inactive'] as $key => $label)
                    @php $tabParams = array_filter(['status' => $key ?: null, 'search' => ($search ?? '') ?: null]); @endphp
                    <a href="{{ route('mentor.videos.index', $tabParams) }}"
                       class="session-filter-tab {{ ($status ?? '') === $key ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>
            <div class="assess-toolbar__grid">
                @if($status ?? '')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <div class="assess-toolbar__search session-search-field">
                    <span class="session-search-icon" aria-hidden="true">🔍</span>
                    <input type="search" name="search" class="form-input" value="{{ $search ?? '' }}"
                           placeholder="Search collections…" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-outline assess-toolbar__submit">Search</button>
            </div>
        </form>

        <div class="assess-table-wrap">
            <table class="assess-table assess-table--assessments">
                <thead>
                    <tr>
                        <th class="num">Sr.</th>
                        <th>Collection</th>
                        <th>Videos</th>
                        <th>Status</th>
                        <th class="actions">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($collections as $index => $item)
                    <tr>
                        <td class="num">{{ $collections->firstItem() + $index }}</td>
                        <td class="assess-title-cell">
                            <div style="font-weight:700;">{{ $item->name }}</div>
                            @if($item->description)
                            <div class="assess-desc-cell" style="margin-top:4px;">{{ \Illuminate\Support\Str::limit(strip_tags($item->description), 90) }}</div>
                            @endif
                        </td>
                        <td>{{ $item->files->count() }} file{{ $item->files->count() === 1 ? '' : 's' }}</td>
                        <td>
                            @if($item->is_active)
                            <span class="assess-badge is-active">Active</span>
                            @else
                            <span class="assess-badge is-inactive">Inactive</span>
                            @endif
                        </td>
                        <td class="actions">
                            <div class="assess-actions">
                                <a href="{{ route('mentor.videos.show', $item->id) }}" class="assess-icon-btn assess-icon-btn--view" title="View">👁</a>
                                <a href="{{ route('mentor.videos.edit', $item->id) }}" class="assess-icon-btn assess-icon-btn--edit" title="Edit">✎</a>
                                <form method="POST" action="{{ route('mentor.videos.destroy', $item->id) }}"
                                      onsubmit="return confirm('Delete this video collection?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="assess-icon-btn assess-icon-btn--delete" title="Delete">🗑</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="assess-empty">
                                <div style="font-size:40px;">🎬</div>
                                <h3>No videos yet</h3>
                                <p>Upload a collection of videos for mentees to watch.</p>
                                <a href="{{ route('mentor.videos.create') }}" class="btn btn-primary" style="margin-top:14px;">Upload videos</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('frontend.partials.pagination', ['paginator' => $collections])
    </div>
</div>
@endsection
