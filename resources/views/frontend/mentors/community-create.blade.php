@extends('frontend.layouts.app')
@section('title', 'Create Channel — Vedrix Mentor')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">Create Channel</div>
            <div class="dash-subtitle">Create a community channel for your mentees.</div>
        </div>

        <div class="card" style="max-width: 720px; margin: 0 auto;">
            @php $backUrl = route('mentor.community'); @endphp
            <div style="margin-bottom:16px;">
                <a href="{{ $backUrl }}" class="btn btn-ghost btn-sm">← Back</a>
            </div>

            <form method="POST" action="{{ route('mentor.community.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Channel Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Career Tips"
                           class="form-input" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" placeholder="ask-a-mentor"
                           pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                           class="form-input">
                    <p class="text-xs text-gray-500 mt-1">URL-friendly id. Leave blank to auto-generate from name.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Icon (emoji)</label>
                    <input type="text" name="icon" value="{{ old('icon', '💬') }}" maxlength="4" class="form-input" style="max-width: 120px;">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" rows="3" placeholder="What is this channel about?" class="form-input" style="resize:none;">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Category</label>
                    <select name="category" class="form-input">
                        <option value="">Select category</option>
                        @foreach(($categories ?? \App\Models\Channel::CATEGORIES) as $key => $label)
                            <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Visibility</label>
                    <div class="flex gap-3" style="flex-wrap:wrap;">
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
                    'hint' => 'Channel cover image shown on cards and in the channel header. JPEG, PNG, WebP, GIF · max 5MB.',
                ])

                <button type="submit" class="btn btn-primary w-full" style="margin-top:10px;">Create Channel</button>
            </form>
        </div>
    </div>
</div>
@push('scripts')
@include('partials.community-composer-scripts')
@endpush

@endsection

