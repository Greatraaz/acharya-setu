@extends('admin.layouts.app')
@section('title', $mentee->name)
@section('heading', 'Mentee Profile')
@section('content')

<div class="max-w-5xl space-y-5">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.mentees.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Mentees
        </a>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.mentees.toggle-status', $mentee) }}">
                @csrf
                <button type="submit"
                        class="text-xs font-medium px-3 py-2 rounded-xl border transition-colors
                        {{ $mentee->is_active ? 'bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200' : 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100' }}">
                    {{ $mentee->is_active ? 'Deactivate Account' : 'Activate Account' }}
                </button>
            </form>
            <form method="POST" action="{{ route('admin.mentees.destroy', $mentee) }}"
                  onsubmit="return confirm('Delete {{ addslashes($mentee->name) }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs font-medium px-3 py-2 rounded-xl border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                    Delete Account
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">✓ {{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-3 gap-5">

        {{-- Left: Main profile --}}
        <div class="col-span-2 space-y-5">

            {{-- Profile card --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="flex items-start gap-5 p-6 border-b border-gray-100">
                    @if($mentee->avatar_url)
                    <img src="{{ $mentee->avatar_url }}" class="w-16 h-16 rounded-2xl object-cover flex-shrink-0">
                    @else
                    <div class="w-16 h-16 rounded-2xl bg-emerald-100 text-emerald-700 font-bold text-xl flex items-center justify-center flex-shrink-0">
                        {{ strtoupper(substr($mentee->name, 0, 2)) }}
                    </div>
                    @endif
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h2 class="text-lg font-bold text-gray-900">{{ $mentee->name }}</h2>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $mentee->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $mentee->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if(!$mentee->onboarding_completed)
                            <span class="text-xs font-semibold bg-amber-50 text-amber-700 px-2.5 py-1 rounded-full">Onboarding incomplete</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500">{{ $mentee->email }}</p>
                        @if($mentee->phone)<p class="text-xs text-gray-400 mt-0.5">{{ $mentee->phone }}</p>@endif
                    </div>
                </div>

                <div class="p-6 grid grid-cols-2 gap-4">
                    @foreach([
                        ['College',   $mentee->college ?? '—'],
                        ['Field',     $mentee->field ?? '—'],
                        ['Year',      $mentee->year ?? '—'],
                        ['Gender',    $mentee->gender ? ucfirst($mentee->gender) : '—'],
                        ['Stream',    $mentee->education_stream ?? '—'],
                        ['Plan',      ucfirst($mentee->subscription_plan ?? 'free')],
                        ['Sessions',  $mentee->total_sessions],
                        ['Wallet',    '₹' . number_format($mentee->wallet_balance, 2)],
                        ['Joined',    $mentee->created_at->format('d M Y')],
                        ['Onboarding', $mentee->onboarding_completed ? 'Complete' : 'Step ' . $mentee->onboarding_step . '/4'],
                    ] as [$label, $value])
                    <div class="bg-gray-50 rounded-xl px-4 py-3">
                        <div class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-0.5">{{ $label }}</div>
                        <div class="text-sm font-semibold text-gray-800">{{ $value }}</div>
                    </div>
                    @endforeach
                </div>

                @if($mentee->career_goals && count((array)$mentee->career_goals))
                <div class="px-6 pb-6">
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-2">Career Goals</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach((array)$mentee->career_goals as $goal)
                        <span class="text-xs bg-blue-50 text-blue-700 border border-blue-100 px-2.5 py-1 rounded-full">{{ $goal }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($mentee->strengths && count((array)$mentee->strengths))
                <div class="px-6 pb-6">
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-2">Strengths</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach((array)$mentee->strengths as $s)
                        <span class="text-xs bg-violet-50 text-violet-700 border border-violet-100 px-2.5 py-1 rounded-full">{{ $s }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Recent sessions --}}
            @if($mentee->menteeSessions->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Recent Sessions</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach($mentee->menteeSessions->take(5) as $session)
                    @php
                    $sc = ['completed'=>'text-green-700 bg-green-50','ongoing'=>'text-blue-700 bg-blue-50','pending'=>'text-amber-700 bg-amber-50','cancelled'=>'text-red-600 bg-red-50'];
                    @endphp
                    <div class="flex items-center gap-3 px-5 py-3">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-800 truncate">{{ $session->title }}</div>
                            <div class="text-xs text-gray-400">{{ $session->scheduled_at->format('d M Y, H:i') }} · {{ $session->mentor->name }}</div>
                        </div>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $sc[$session->status] ?? 'bg-gray-100 text-gray-500' }}">{{ ucfirst($session->status) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Right sidebar --}}
        <div class="space-y-4">

            {{-- Mentor assignment card --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Assigned Mentor</h3>
                </div>
                <div class="p-5">
                    @if($mentee->assignedMentor)
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 font-bold text-sm flex items-center justify-center flex-shrink-0">
                            {{ strtoupper(substr($mentee->assignedMentor->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">{{ $mentee->assignedMentor->name }}</div>
                            <div class="text-xs text-gray-500">{{ $mentee->assignedMentor->designation ?? 'Mentor' }}</div>
                            @if($mentee->assignedMentor->rating > 0)
                            <div class="text-xs text-amber-600 font-medium mt-0.5">★ {{ number_format($mentee->assignedMentor->rating, 1) }}</div>
                            @endif
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.mentees.assign-mentor', $mentee) }}" class="space-y-2">
                        @csrf
                        <div class="relative">
                            <select name="mentor_id"
                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2 pr-8 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 appearance-none cursor-pointer">
                                <option value="">Remove assignment</option>
                                @foreach(\App\Models\User::mentors()->active()->approved()->orderBy('name')->get() as $mentor)
                                <option value="{{ $mentor->id }}" {{ $mentee->assigned_mentor_id == $mentor->id ? 'selected' : '' }}>{{ $mentor->name }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </span>
                        </div>
                        <button type="submit" class="w-full text-xs font-semibold text-blue-600 bg-blue-50 border border-blue-100 hover:bg-blue-100 py-2 rounded-xl transition-colors">
                            Update Mentor
                        </button>
                    </form>
                    @else
                    <p class="text-sm text-gray-400 mb-3 text-center italic">No mentor assigned</p>
                    <form method="POST" action="{{ route('admin.mentees.assign-mentor', $mentee) }}" class="space-y-2">
                        @csrf
                        <div class="relative">
                            <select name="mentor_id" required
                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2 pr-8 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 appearance-none cursor-pointer">
                                <option value="">Select mentor…</option>
                                @foreach(\App\Models\User::mentors()->active()->approved()->orderBy('name')->get() as $mentor)
                                <option value="{{ $mentor->id }}">{{ $mentor->name }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </span>
                        </div>
                        <button type="submit" class="w-full text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 py-2.5 rounded-xl transition-colors">
                            Assign Mentor
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Quick info --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Quick Info</h3>
                <div class="space-y-2 text-xs">
                    @foreach([
                        ['User ID',     '#' . $mentee->id],
                        ['Wallet',      '₹' . number_format($mentee->wallet_balance, 2)],
                        ['Total Sessions', $mentee->total_sessions],
                        ['Rating',      $mentee->rating > 0 ? '★ ' . number_format($mentee->rating, 1) : '—'],
                        ['Plan',        ucfirst($mentee->subscription_plan ?? 'free')],
                        ['Last Updated', $mentee->updated_at->format('d M Y')],
                    ] as [$l, $v])
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-50 last:border-0">
                        <span class="text-gray-400 font-medium">{{ $l }}</span>
                        <span class="text-gray-700 font-semibold font-mono">{{ $v }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@endsection