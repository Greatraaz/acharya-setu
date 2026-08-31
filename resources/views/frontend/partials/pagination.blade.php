@if(isset($paginator) && method_exists($paginator, 'hasPages') && $paginator->hasPages())
<div class="dash-pagination">
    {{ $paginator->withQueryString()->links('vendor.pagination.frontend') }}
</div>
@endif
