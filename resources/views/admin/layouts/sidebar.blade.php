<aside id="admin-sidebar" class="fixed left-0 top-0 h-screen w-72 max-w-[85vw] bg-white border-r border-slate-200 shadow-soft z-50 flex flex-col lg:translate-x-0">
    <div class="px-5 pt-5 pb-4 border-b border-slate-100">
        <img src="{{ asset('admin/images/logo.png') }}" alt="Vedrix" class="w-[200px] max-w-full h-auto object-contain">
        <p class="mt-2 text-xl font-bold text-black">Admin Platform</p>
    </div>

    <div class="px-5 pt-3 pb-6 overflow-y-auto flex-1 space-y-1">

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
                ['admin.withdrawals.index',  '🏦', 'Withdrawals'],
                ['admin.call-logs.index',  '📞', 'Call Records'],
            ],
            'Curriculum' => [
                ['admin.mentor-approvals.index', '✅', 'Mentor Approvals'],
                ['admin.curriculum.catalog',    '📚', 'Curriculum Streams'],
                ['admin.curriculum.streams',    '🗺️', '6-Month Journey Manager'],
            ],
            'Content' => [
                ['admin.quizzes.index',      '🎯', 'Quizzes & MCQs'],
                ['admin.assessments.index',  '📝', 'Assessments Categories'],
                ['admin.assessment-questions.index',  '❓', 'Assessment Questions'],
                ['admin.jobs.index',         '💼', 'Job Listings'],
                // ['admin.wellness.index',     '🧘', 'Wellness Surveys'],
                ['admin.community.index',    '💬', 'Community Channels'],
                ['admin.plans.index', '⭐', 'Premium Plans'],
                ['admin.subscriptions.index', '📋', 'Subscriptions'],
            ],
            'Utilities' => [
                'type' => 'group',
                'icon' => '📦',
                'items' => collect(\App\Support\InsightsMenu::items())->map(function ($item) {
                    $route = ($item['key'] ?? '') === 'blogs' ? 'admin.blogs.index' : '#';

                    return [$route, $item['icon'], $item['label']];
                })->all(),
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

            @if(isset($menus['type']) && $menus['type'] === 'group')
                @php $groupId = 'admin-nav-'.\Illuminate\Support\Str::slug($section); @endphp
                <div class="{{ $loop->first ? 'pt-1' : 'pt-4' }}">
                    @php
                        $groupOpen = collect($menus['items'])->contains(function ($item) use ($current) {
                            $target = $item[0] ?? '';
                            return $target !== '#' && str_starts_with((string) $current, (string) $target);
                        });
                    @endphp
                    <button type="button"
                            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-2xl transition-all font-medium text-slate-600 hover:bg-slate-50"
                            data-admin-nav-toggle="{{ $groupId }}"
                            aria-expanded="{{ $groupOpen ? 'true' : 'true' }}"
                            aria-controls="{{ $groupId }}">
                        <span class="text-base">{{ $menus['icon'] ?? '📦' }}</span>
                        <span class="text-sm">{{ $section }}</span>
                        <svg class="ml-auto w-4 h-4 text-slate-400 transition-transform duration-200" data-admin-nav-chevron fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div id="{{ $groupId }}" class="mt-1 mb-1 space-y-0.5 pl-3 border-l border-slate-100 ml-5">
                        @foreach($menus['items'] as $item)
                            @php
                                [$target, $icon, $label] = $item;
                                $isRoute = $target !== '#' && ! str_starts_with((string) $target, '#');
                                try {
                                    $href = $isRoute ? route($target) : $target;
                                    $isActive = $isRoute && str_starts_with((string) $current, (string) $target);
                                } catch (\Exception $e) {
                                    $href = '#';
                                    $isActive = false;
                                }
                            @endphp
                            <a href="{{ $href }}"
                               class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-medium transition-all
                                      {{ $isActive
                                          ? 'bg-orange-50 text-orange-600'
                                          : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                                <span class="text-sm leading-none w-5 text-center shrink-0">{{ $icon }}</span>
                                <span>{{ $label }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- Section Divider --}}
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-2 pb-1 {{ $loop->first ? 'pt-1' : 'pt-4' }}">
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
            @endif

        @endforeach

    </div>
</aside>

<script>
(function () {
    document.querySelectorAll('[data-admin-nav-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-admin-nav-toggle');
            var panel = document.getElementById(id);
            if (!panel) return;
            var open = btn.getAttribute('aria-expanded') !== 'false';
            btn.setAttribute('aria-expanded', open ? 'false' : 'true');
            panel.classList.toggle('hidden', open);
            var chevron = btn.querySelector('[data-admin-nav-chevron]');
            if (chevron) chevron.style.transform = open ? 'rotate(-90deg)' : '';
        });
    });
})();
</script>