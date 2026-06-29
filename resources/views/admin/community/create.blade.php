@extends('admin.layouts.app')
@section('title', 'Create Channel')
 
@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.community.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back</a>
        <h1 class="font-display text-2xl font-bold text-gray-900 mt-2">Create Channel</h1>
    </div>
 
    <div class="bg-white border border-gray-100 rounded-2xl p-6">
        <form method="POST" action="{{ route('admin.community.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Channel Name</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. general"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
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
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Visibility</label>
                <div class="flex gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="public" {{ old('type','public')==='public' ? 'checked' : '' }} class="accent-blue-600">
                        <span class="text-sm text-gray-700">Public</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="private" {{ old('type')==='private' ? 'checked' : '' }} class="accent-blue-600">
                        <span class="text-sm text-gray-700">Private</span>
                    </label>
                </div>
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 text-white py-2.5 rounded-xl font-medium hover:bg-blue-700 transition-colors">
                Create Channel
            </button>
        </form>
    </div>
</div>
@endsection