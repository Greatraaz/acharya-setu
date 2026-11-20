@php
    $current = request()->route()?->getName();
@endphp
<div class="flex flex-wrap gap-2 mb-6">
    @foreach([
        ['admin.assessments.index', 'admin.assessments.', 'Assessments'],
        ['admin.assessment-categories.index', 'admin.assessment-categories.', 'Question Groups'],
        ['admin.assessment-questions.index', 'admin.assessment-questions.', 'Questions'],
    ] as [$route, $prefix, $label])
    @php $isActive = str_starts_with((string) $current, $prefix); @endphp
    <a href="{{ route($route) }}"
       class="px-4 py-2 rounded-xl text-sm font-medium border transition whitespace-nowrap
              {{ $isActive
                    ? 'bg-amber-500 text-white border-amber-500'
                    : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
        {{ $label }}
    </a>
    @endforeach
</div>
