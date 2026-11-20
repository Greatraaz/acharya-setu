@extends('frontend.layouts.app')
@section('title', 'Videos — Vedrix Mentor')

@push('styles')
<style>
.mv-mentor{
    --mv: var(--brand, #f59e0b);
    --mv-dark: var(--brand-dark, #d97706);
    --mv-soft: var(--brand-muted, rgba(245,158,11,.12));
    font-family: var(--font-body, "DM Sans", sans-serif);
}
.mv-mentor .dash-title{ font-family: var(--font-head, "Sora", sans-serif); letter-spacing:-.02em; }
.mv-mentor__intro{
    position:relative; overflow:hidden;
    margin:4px 0 18px; padding:20px 22px;
    border:1px solid var(--border, rgba(0,0,0,.07));
    border-radius: var(--radius-lg, 16px);
    background:
        radial-gradient(480px 160px at 100% 0%, color-mix(in srgb, var(--mv) 16%, transparent), transparent 70%),
        var(--card-bg, #fff);
    box-shadow: var(--shadow, 0 4px 24px rgba(0,0,0,.06));
}
.mv-mentor__intro::before{
    content:""; position:absolute; left:0; top:0; bottom:0; width:4px;
    background:linear-gradient(180deg, var(--mv), var(--mv-dark));
}
.mv-mentor__eyebrow{
    display:inline-flex; font-family: var(--font-head, "Sora", sans-serif);
    font-size:11px; font-weight:700; letter-spacing:.12em; text-transform:uppercase;
    color:var(--mv-dark); background:var(--mv-soft); padding:6px 11px; border-radius:8px;
}
.mv-mentor__intro h2{
    margin:12px 0 6px; font-family: var(--font-head, "Sora", sans-serif);
    font-size:clamp(22px, 3vw, 28px); font-weight:800; letter-spacing:-.03em; color:var(--text, #09090b);
}
.mv-mentor__intro p{ margin:0; max-width:520px; font-size:14px; line-height:1.55; color:var(--text-2, #52525b); }
</style>
@endpush

@section('content')
<div class="dash-layout assess-page mv-mentor">
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

        <div class="mv-mentor__intro">
            <span class="mv-mentor__eyebrow">Content</span>
            <h2>Video libraries for mentees</h2>
            <p>Upload collections your mentees can watch anytime. Keep them active when ready to share.</p>
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
                            <div style="font-size:12px;color:var(--text-3);margin-top:2px;">{{ \Illuminate\Support\Str::limit($item->description, 80) }}</div>
                            @endif
                        </td>
                        <td>{{ $item->files->count() }}</td>
                        <td>
                            <span class="session-status {{ $item->is_active ? 'completed' : 'pending' }}">
                                {{ $item->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="actions">
                            <a href="{{ route('mentor.videos.show', $item) }}" class="btn btn-ghost btn-sm">View</a>
                            <a href="{{ route('mentor.videos.edit', $item) }}" class="btn btn-ghost btn-sm">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:36px;color:var(--text-3);">
                            No video collections yet. Upload your first set to share with mentees.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($collections->hasPages())
        <div style="margin-top:16px;">@include('frontend.partials.pagination', ['paginator' => $collections])</div>
        @endif
    </div>
</div>
@endsection
