@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="pagination">
        @if ($paginator->onFirstPage())
            <span class="page-btn disabled" aria-disabled="true">‹</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="page-btn" rel="prev" aria-label="Previous page">‹</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="page-btn disabled" aria-hidden="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-btn active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="page-btn" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="page-btn" rel="next" aria-label="Next page">›</a>
        @else
            <span class="page-btn disabled" aria-disabled="true">›</span>
        @endif
    </nav>
@endif
