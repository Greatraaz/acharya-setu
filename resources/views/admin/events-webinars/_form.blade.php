@php
    $isEdit = isset($event) && $event->exists;
    $formAction = $isEdit
        ? route('admin.events-webinars.update', $event)
        : route('admin.events-webinars.store');
    $startTime = old('start_time', $event->start_time ? substr((string) $event->start_time, 0, 5) : '');
    $endTime = old('end_time', $event->end_time ? substr((string) $event->end_time, 0, 5) : '');
@endphp

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-6"
      onsubmit="if (window.tinymce) { tinymce.triggerSave(); }">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Type *</label>
                <select name="type" required
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(\App\Models\InsightEvent::TYPES as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $event->type ?? 'webinar') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Status *</label>
                <select name="status" required
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="active" @selected(old('status', $event->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $event->status ?? '') === 'inactive')>Inactive</option>
                </select>
                @error('status')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Title *</label>
            <input type="text" name="title" value="{{ old('title', $event->title ?? '') }}" required
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                   placeholder="Enter title">
            @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Speaker *</label>
                <input type="text" name="speaker" value="{{ old('speaker', $event->speaker ?? '') }}" required
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                       placeholder="Speaker name">
                @error('speaker')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Location *</label>
                <input type="text" name="location" value="{{ old('location', $event->location ?? '') }}" required
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                       placeholder="City or Online">
                @error('location')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Image {{ $isEdit ? '' : '*' }}</label>
            <input type="file" name="image" accept="image/*" {{ $isEdit ? '' : 'required' }}
                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white">
            @if($isEdit && $event->imageUrl())
                <img src="{{ $event->imageUrl() }}" alt="" class="mt-2 h-20 rounded-lg object-cover border border-gray-100">
            @endif
            @error('image')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Start Date *</label>
                <input type="date" name="start_date" value="{{ old('start_date', optional($event->start_date ?? null)->format('Y-m-d')) }}" required
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                @error('start_date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">End Date *</label>
                <input type="date" name="end_date" value="{{ old('end_date', optional($event->end_date ?? null)->format('Y-m-d')) }}" required
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                @error('end_date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Start Time *</label>
                <input type="time" name="start_time" value="{{ $startTime }}" required
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                @error('start_time')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">End Time *</label>
                <input type="time" name="end_time" value="{{ $endTime }}" required
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                @error('end_time')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Description *</label>
            <textarea name="description" id="event-description" rows="10"
                      class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">{{ old('description', $event->description ?? '') }}</textarea>
            @error('description')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Event Agenda</label>
            <textarea name="event_agenda" id="event-agenda" rows="8"
                      class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">{{ old('event_agenda', $event->event_agenda ?? '') }}</textarea>
            @error('event_agenda')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Who Should Attend</label>
            <textarea name="who_should_attend" id="event-who-attend" rows="8"
                      class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">{{ old('who_should_attend', $event->who_should_attend ?? '') }}</textarea>
            @error('who_should_attend')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">What You Will Learn</label>
            <textarea name="what_you_will_learn" id="event-learn" rows="8"
                      class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm">{{ old('what_you_will_learn', $event->what_you_will_learn ?? '') }}</textarea>
            @error('what_you_will_learn')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">FAQ</label>
            <textarea name="faq" rows="4"
                      class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm"
                      placeholder="Enter FAQ as plain text. One question per line.">{{ old('faq', $event->faq ?? '') }}</textarea>
            @error('faq')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.events-webinars.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50">Cancel</a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition">
            {{ $isEdit ? 'Update' : 'Submit' }}
        </button>
    </div>
</form>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: '#event-description, #event-agenda, #event-who-attend, #event-learn',
    height: 280,
    menubar: true,
    plugins: 'lists link image table code wordcount',
    toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | removeformat',
    branding: false,
    content_style: 'body { font-family: Inter, system-ui, sans-serif; font-size: 14px; }'
});
</script>
@endpush
