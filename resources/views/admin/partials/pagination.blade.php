@if(isset($paginator) && method_exists($paginator, 'hasPages') && $paginator->hasPages())
<div class="px-5 py-4 border-t border-gray-100">
    {{ $paginator->withQueryString()->links() }}
</div>
@endif
