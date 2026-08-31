@php
    $action = $action ?? url()->current();
    $search = $search ?? request('search', request('q', ''));
    $placeholder = $placeholder ?? 'Search…';
    $preserve = $preserve ?? [];
@endphp

<form method="GET" action="{{ $action }}" class="insights-search-bar">
    @foreach($preserve as $key => $value)
        @if($value !== null && $value !== '')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
    <div class="insights-search-bar__field">
        <span class="session-search-icon" aria-hidden="true">🔍</span>
        <input type="search" name="search" class="form-input" value="{{ $search }}"
               placeholder="{{ $placeholder }}" autocomplete="off">
    </div>
    <button type="submit" class="btn btn-outline btn-sm">Search</button>
    @if($search !== '')
        <a href="{{ $action . '?' . http_build_query(array_filter($preserve, fn ($v) => $v !== null && $v !== '')) }}" class="btn btn-ghost btn-sm">Clear</a>
    @endif
</form>
