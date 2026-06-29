<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4 pb-4 border-b">
        <div class="w-16 h-16 rounded-full bg-orange-100 flex items-center justify-center text-2xl font-bold text-orange-600">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div>
            <h3 class="text-lg font-bold text-slate-800">{{ $user->name }}</h3>
            <p class="text-sm text-slate-500">{{ $user->email }}</p>
            <div class="flex gap-2 mt-1">
                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">
                    {{ ucfirst($user->role) }}
                </span>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                    {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Common Info --}}
    <div class="grid grid-cols-2 gap-3 text-sm">
        @foreach([
            'Phone'   => $user->phone,
            'Gender'  => ucfirst($user->gender ?? '—'),
            'Joined'  => $user->created_at?->format('d M Y'),
            'LinkedIn'=> $user->linkedin,
        ] as $label => $value)
        <div class="bg-slate-50 rounded-xl p-3">
            <p class="text-xs text-slate-400 mb-1">{{ $label }}</p>
            <p class="font-medium text-slate-700">{{ $value ?: '—' }}</p>
        </div>
        @endforeach
    </div>

    @if($user->bio)
    <div class="bg-slate-50 rounded-xl p-3 text-sm">
        <p class="text-xs text-slate-400 mb-1">Bio</p>
        <p class="text-slate-700">{{ $user->bio }}</p>
    </div>
    @endif

    {{-- Mentor Details --}}
    @if($user->isMentor())
    <div>
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Mentor Details</p>
        <div class="grid grid-cols-2 gap-3 text-sm">
            @foreach([
                'Company'     => $user->company,
                'Designation' => $user->designation,
                'Expertise'   => $user->expertise,
                'Experience'  => $user->experience_years ? $user->experience_years . ' yrs' : null,
                'Rate/Min'    => $user->rate_per_minute ? '₹' . $user->rate_per_minute : null,
                'Rating'      => $user->rating ? '⭐ ' . $user->rating : null,
                'Sessions'    => $user->total_sessions,
                'Status'      => ucfirst($user->mentor_status ?? '—'),
            ] as $label => $value)
            <div class="bg-slate-50 rounded-xl p-3">
                <p class="text-xs text-slate-400 mb-1">{{ $label }}</p>
                <p class="font-medium text-slate-700">{{ $value ?: '—' }}</p>
            </div>
            @endforeach
        </div>

        @if($user->mentees->count())
        <div class="mt-3">
            <p class="text-xs text-slate-400 mb-2">Assigned Mentees ({{ $user->mentees->count() }})</p>
            <div class="flex flex-wrap gap-2">
                @foreach($user->mentees as $mentee)
                    <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs">
                        {{ $mentee->name }}
                    </span>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Mentee Details --}}
    @if($user->isMentee())
    <div>
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Mentee Details</p>
        <div class="grid grid-cols-2 gap-3 text-sm">
            @foreach([
                'College'      => $user->college,
                'Field'        => $user->field,
                'Year'         => $user->year,
                'Plan'         => $user->subscription_plan,
                'Mentor'       => $user->assignedMentor?->name,
                'Onboarding'   => $user->onboarding_completed ? 'Completed' : "Step {$user->onboarding_step}",
            ] as $label => $value)
            <div class="bg-slate-50 rounded-xl p-3">
                <p class="text-xs text-slate-400 mb-1">{{ $label }}</p>
                <p class="font-medium text-slate-700">{{ $value ?: '—' }}</p>
            </div>
            @endforeach
        </div>

        @if($user->career_goals)
        <div class="mt-3 bg-slate-50 rounded-xl p-3 text-sm">
            <p class="text-xs text-slate-400 mb-1">Career Goals</p>
            <p class="text-slate-700">{{ $user->career_goals }}</p>
        </div>
        @endif

        @if($user->strengths)
        <div class="mt-3 bg-slate-50 rounded-xl p-3 text-sm">
            <p class="text-xs text-slate-400 mb-1">Strengths</p>
            <p class="text-slate-700">{{ $user->strengths }}</p>
        </div>
        @endif
    </div>
    @endif

</div>