@php
    $current = request()->route()?->getName();
@endphp
<div class="flex flex-wrap gap-2 mb-6">
    @foreach([
        ['admin.assessments', 'Categories'],
        ['admin.assessment-questions', 'Questions'],
    ] as [$prefix, $label])
    @php
        $isActive = str_starts_with($current, $prefix);
        $href = $prefix === 'admin.assessments'
            ? route('admin.assessments.index')
            : route('admin.assessment-questions.index');
    @endphp
    <a href="{{ $href }}"
       class="px-4 py-2 rounded-xl text-sm font-medium border transition whitespace-nowrap
              {{ $isActive
                    ? 'bg-blue-600 text-white border-blue-600'
                    : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
        {{ $label }}
    </a>
    @endforeach
</div>
