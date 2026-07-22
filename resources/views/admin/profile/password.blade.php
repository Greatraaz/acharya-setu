@extends('admin.layouts.app')
@section('title', 'Change Password')
@section('heading', 'Change Password')
@section('content')

<div class="max-w-xl space-y-6">
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
            <h2 class="text-sm font-semibold text-gray-800">Update your password</h2>
            <p class="text-xs text-gray-500 mt-0.5">Use a strong password with at least 8 characters.</p>
        </div>

        <form method="POST" action="{{ route('admin.profile.password.update') }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Current Password <span class="text-red-500">*</span></label>
                <input type="password" name="current_password" required
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">New Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" required
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm New Password <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" required
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                    Update Password
                </button>
                <a href="{{ route('admin.profile.show') }}"
                   class="px-4 py-2.5 border border-gray-200 text-gray-600 text-sm rounded-xl hover:bg-gray-50 transition-colors">
                    Back to Profile
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
