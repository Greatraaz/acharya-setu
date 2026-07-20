@extends('admin.layouts.app')
@section('title', 'Edit Mentee — ' . $mentee->name)
@section('heading', 'Edit Mentee Profile')
@section('content')

<div class="flex items-center gap-2 mb-6 text-sm">
    <a href="{{ route('admin.mentees.show', $mentee) }}" class="text-gray-400 hover:text-gray-700">← {{ $mentee->name }}</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-600 font-medium">Edit</span>
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4 mb-6 text-sm text-red-700">
    <ul class="space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('admin.mentee.update', $mentee) }}" enctype="multipart/form-data" class="max-w-5xl" id="mentee-onboarding-form">
    @csrf @method('PUT')
    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-5">
            @include('admin.mentees._onboarding-fields', [
                'mode' => 'edit',
                'mentee' => $mentee,
                'streams' => $streams,
                'tracks' => $tracks,
                'preferences' => $preferences,
            ])
        </div>

        <div class="space-y-5">
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Account Active</p>
                        <p class="text-xs text-gray-400 mt-0.5">Allow login and access</p>
                    </div>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $mentee->is_active) ? 'checked' : '' }} class="rounded text-emerald-600">
                    </label>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Assignment & Plan</h3>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Assigned Mentor</label>
                        <select name="assigned_mentor_id" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white">
                            <option value="">No mentor assigned</option>
                            @foreach($mentors as $mentor)
                            <option value="{{ $mentor->id }}" {{ old('assigned_mentor_id', $mentee->assigned_mentor_id) == $mentor->id ? 'selected' : '' }}>
                                {{ $mentor->name }} — {{ $mentor->designation ?? $mentor->field ?? 'Mentor' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="hidden" name="auto_assign_mentor" value="0">
                        <input type="checkbox" name="auto_assign_mentor" value="1" class="rounded text-emerald-600">
                        Auto-assign mentor if none selected
                    </label>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Subscription Plan</label>
                        <select name="subscription_plan" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white">
                            @foreach(['free'=>'Free','basic'=>'Basic','pro'=>'Pro','enterprise'=>'Enterprise'] as $v=>$l)
                            <option value="{{ $v }}" {{ old('subscription_plan', $mentee->subscription_plan ?? 'free') === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-5 text-xs space-y-2">
                <div class="flex justify-between"><span class="text-gray-400">Onboarding</span><span class="font-semibold">{{ $mentee->onboarding_completed ? 'Complete' : 'Step '.$mentee->onboarding_step.'/4' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Joined</span><span class="font-semibold">{{ $mentee->created_at->format('d M Y') }}</span></div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5 flex gap-2">
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2.5 rounded-xl">Save Changes</button>
                <a href="{{ route('admin.mentees.show', $mentee) }}" class="px-4 py-2.5 text-sm text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection
