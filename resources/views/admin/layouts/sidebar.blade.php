<aside class="fixed left-0 top-0 h-screen w-72 bg-white border-r border-slate-200 shadow-soft z-50 flex flex-col">
    <div class="rounded-2xl bg-white/20 flex items-center justify-center text-2xl">
        <img src="{{ asset('admin/images/logo.jpg') }}" alt="AcharyaSetu" class="h-16 mx-auto object-contain mb-4">
    </div>
    <div>
        <h2 class="font-bold text-xl">Admin Platform</h2>
    </div>

    <div class="px-5 py-6 overflow-y-auto flex-1 space-y-1">

        @php
        $current = request()->route()->getName();

        $menuSections = [
            'Overview' => [
                ['admin.dashboard',         '📊', 'Dashboard'],
            ],
            'Users' => [
                ['admin.mentors.index',      '👨‍💼', 'Mentors'],
                ['admin.mentees.index',     '🎓', 'Mentee'],
            ],
            'Activity' => [
                ['admin.sessions.index',     '📅', 'Sessions'],
                ['admin.wallet.index',       '💰', 'Wallet'],
                ['admin.call-logs.index',  '📞', 'Call Records'],
            ],
            'Curriculum' => [
                ['admin.mentor-approvals.index', '✅', 'Mentor Approvals'],
                ['admin.curriculum.streams',   '🗺️', '6-Month Journey Manager'],
            ],
            'Content' => [
                ['admin.quizzes.index',      '🎯', 'Quizzes & MCQs'],
                ['admin.jobs.index',         '💼', 'Job Listings'],
                ['admin.wellness.index',     '🧘', 'Wellness Surveys'],
                ['admin.community.index',    '💬', 'Community Channels'],
                ['admin.plans.index', '⭐', 'Premium Plans'],
            ],
            'Configuration' => [
                ['admin.settings.index',  '⚙️', 'App Settings'],
            ],
            'Log Activity' => [
                ['admin.logs.index',  '📜', 'Logs'],
           
            ],
        ];
        @endphp

        @foreach($menuSections as $section => $menus)

            {{-- Section Divider --}}
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-2 pt-4 pb-1">
                {{ $section }}
            </p>

            @foreach($menus as [$route, $icon, $label])
                @php
                    try {
                        $href = route($route);
                        $isActive = str_starts_with($current, $route);
                    } catch (\Exception $e) {
                        $href = '#';
                        $isActive = false;
                    }
                @endphp

                <a href="{{ $href }}"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-2xl transition-all font-medium
                          {{ $isActive
                              ? 'bg-orange-50 text-orange-600 shadow-sm'
                              : 'text-slate-600 hover:bg-slate-50' }}">

                    <span class="text-base">{{ $icon }}</span>
                    <span class="text-sm">{{ $label }}</span>

                    @if($isActive)
                        <span class="ml-auto w-2 h-2 rounded-full bg-orange-500"></span>
                    @endif
                </a>
            @endforeach

        @endforeach

    </div>
</aside>