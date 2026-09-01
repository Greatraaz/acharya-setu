@php
    $routeName = $routeName ?? 'mentee.community.index';
    $search = $search ?? request('search', request('q', ''));
    $type = $type ?? request('type', '');
    $category = $category ?? request('category', '');
    $joinedRaw = $joined ?? request('joined');
    $joinedKey = $joinedRaw === null || $joinedRaw === '' ? 'all' : ($joinedRaw === '1' || $joinedRaw === true || $joinedRaw === 1 ? 'joined' : 'not_joined');

    $baseParams = array_filter([
        'search' => $search !== '' ? $search : null,
        'type' => in_array($type, ['public', 'private'], true) ? $type : null,
        'category' => $category !== '' ? $category : null,
    ], fn ($v) => $v !== null && $v !== '');

    $tabParams = fn (string $key) => array_filter(array_merge($baseParams, match ($key) {
        'all' => [],
        'joined' => ['joined' => 1],
        'not_joined' => ['joined' => 0],
        default => [],
    }), fn ($v) => $v !== null && $v !== '');
@endphp

<form method="GET" action="{{ route($routeName) }}" class="session-toolbar">
    <div class="session-filter-tabs">
        @foreach(['all' => 'All', 'joined' => 'Joined', 'not_joined' => 'Not joined'] as $key => $label)
            <a href="{{ route($routeName, $tabParams($key)) }}"
               class="session-filter-tab {{ $joinedKey === $key ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="session-toolbar-controls community-filters-toolbar__controls">
        @if($joinedKey === 'joined')
            <input type="hidden" name="joined" value="1">
        @elseif($joinedKey === 'not_joined')
            <input type="hidden" name="joined" value="0">
        @endif

        <div class="session-search-field">
            <span class="session-search-icon" aria-hidden="true">🔍</span>
            <input type="search" name="search" class="form-input" value="{{ $search }}"
                   placeholder="Search channels…" autocomplete="off">
        </div>

        <select name="type" class="form-input form-select session-date-input" title="Channel type">
            <option value="">All types</option>
            <option value="public" @selected($type === 'public')>Public</option>
            <option value="private" @selected($type === 'private')>Private</option>
        </select>

        <select name="category" class="form-input form-select session-date-input" title="Category">
            <option value="">All categories</option>
            @foreach(\App\Models\Channel::CATEGORIES as $key => $label)
                <option value="{{ $key }}" @selected($category === $key)>{{ $label }}</option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-outline">Search</button>

        @if($search !== '' || $type !== '' || $category !== '' || $joinedKey !== 'all')
            <a href="{{ route($routeName) }}" class="btn btn-ghost">Clear</a>
        @endif
    </div>
</form>

@if(isset($channels) && method_exists($channels, 'total'))
    <p class="session-filter-status">
        {{ $channels->total() }} {{ \Illuminate\Support\Str::plural('channel', $channels->total()) }}
        @if($search !== '')
            matching “{{ $search }}”
        @endif
    </p>
@endif
