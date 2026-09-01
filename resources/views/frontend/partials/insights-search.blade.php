@php
    $action = $action ?? url()->current();
    $search = $search ?? request('search', request('q', ''));
    $placeholder = $placeholder ?? 'Search…';
    $preserve = $preserve ?? [];
    $variant = $variant ?? 'default';
@endphp

<form method="GET" action="{{ $action }}"
      class="insights-search-bar {{ $variant === 'toolbar' ? 'insights-search-bar--toolbar' : '' }}">
    @foreach($preserve as $key => $value)
        @if($value !== null && $value !== '')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
    <div class="insights-search-bar__field">
        <span class="session-search-icon" aria-hidden="true">🔍</span>
        <input type="search" name="search" class="form-input" value="{{ $search }}"
               placeholder="{{ $placeholder }}" autocomplete="off"
               aria-label="{{ $placeholder }}">
        @if($search !== '')
        <a href="{{ $action . '?' . http_build_query(array_filter($preserve, fn ($v) => $v !== null && $v !== '')) }}"
           class="insights-search-bar__clear" aria-label="Clear search" title="Clear search">×</a>
        @endif
    </div>
</form>
