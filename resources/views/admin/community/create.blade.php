@extends('admin.layouts.app')
@section('title', 'Create Channel')

@section('content')
@php
    $r = request()->routeIs('admin.*')
        ? 'admin.community'
        : (request()->routeIs('mentor.*') ? 'mentor.community' : 'mentee.community');
@endphp
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        @php
            $backRoute = null;
            try {
                $backRoute = route($r . '.index');
            } catch (\Throwable $e) {
                // Mentor route names use `mentor.community` (no `.index`)
                $backRoute = route($r);
            }
        @endphp
        <a href="{{ $backRoute }}" class="text-sm text-gray-400 hover:text-gray-600">← Back</a>
        <h1 class="font-display text-2xl font-bold text-gray-900 mt-2">Create Channel</h1>
    </div>

    <div class="bg-white border border-gray-100 rounded-2xl p-6">
        <form method="POST" action="{{ route($r.'.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Channel Name</label>
                <input type="text" name="name" id="channel-name" value="{{ old('name') }}" placeholder="e.g. Ask a Mentor"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Slug</label>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-400">#</span>
                    <input type="text" name="slug" id="channel-slug" value="{{ old('slug') }}" placeholder="ask-a-mentor"
                           pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                           class="flex-1 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <p class="text-xs text-gray-400 mt-1">URL-friendly id. Leave blank to auto-generate from name.</p>
                @error('slug')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Icon (emoji)</label>
                <input type="text" name="icon" value="{{ old('icon', '💬') }}" maxlength="4"
                       class="w-20 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-center text-xl">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea name="description" rows="3" placeholder="What is this channel about?"
                          class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                <select name="category"
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select category</option>
                    @foreach(($categories ?? \App\Models\Channel::CATEGORIES) as $key => $label)
                        <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Groups channels like Discord categories / Slack sections.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Visibility</label>
                <div class="flex gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="public" {{ old('type','public')==='public' ? 'checked' : '' }} class="accent-blue-600">
                        <span class="text-sm text-gray-700">Public - mentors &amp; mentees can join</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="private" {{ old('type')==='private' ? 'checked' : '' }} class="accent-blue-600">
                        <span class="text-sm text-gray-700">Private - invite only</span>
                    </label>
                </div>
            </div>
            @include('partials.community-channel-image-input', [
                'labelClass' => 'block text-sm font-medium text-gray-700 mb-1.5',
                'hintClass' => 'text-xs text-gray-400 mt-1',
                'hint' => 'Channel cover image shown on cards and in the channel header. JPEG, PNG, WebP, GIF · max 5MB.',
            ])
            <button type="submit"
                    class="w-full bg-blue-600 text-white py-2.5 rounded-xl font-medium hover:bg-blue-700 transition-colors">
                Create Channel
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
@include('partials.community-composer-scripts')
<script>
(function () {
    const nameInput = document.getElementById('channel-name');
    const slugInput = document.getElementById('channel-slug');
    if (!nameInput || !slugInput) return;

    let slugTouched = slugInput.value.trim() !== '';
    slugInput.addEventListener('input', () => { slugTouched = true; });

    nameInput.addEventListener('input', function () {
        if (slugTouched) return;
        slugInput.value = this.value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s_]+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    });
})();
</script>
@endpush
