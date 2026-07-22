<header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-slate-200 px-8 py-5">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">@yield('heading','Dashboard')</h1>
            <p class="text-sm text-slate-500 mt-1">Welcome back, manage your platform efficiently.</p>
        </div>
        <div class="flex items-center gap-4">
            @php
                $adminUser = auth()->user();
                try {
                    $notifItems = collect(\App\Http\Controllers\Admin\NotificationController::buildNotifications());
                } catch (\Throwable $e) {
                    $notifItems = collect();
                }
                $notifCount = $notifItems->count();
            @endphp

            <div class="relative" id="admin-notif-menu">
                <button type="button" id="admin-notif-btn"
                        class="relative bg-white border rounded-2xl p-3 hover:shadow-sm transition-shadow">
                    🔔
                    @if($notifCount > 0)
                    <span id="admin-notif-badge"
                          class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center font-semibold">
                        {{ $notifCount > 9 ? '9+' : $notifCount }}
                    </span>
                    @endif
                </button>

                <div id="admin-notif-dropdown"
                     class="hidden absolute right-0 mt-3 w-96 bg-white rounded-2xl border shadow-xl z-50 overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Notifications</p>
                            <p class="text-xs text-slate-400">{{ $notifCount }} pending</p>
                        </div>
                        <a href="{{ route('admin.notifications.index') }}" class="text-xs font-medium text-violet-600 hover:text-violet-700">
                            View all
                        </a>
                    </div>

                    <div class="max-h-96 overflow-y-auto divide-y divide-slate-50">
                        @forelse($notifItems as $item)
                        <a href="{{ $item['url'] }}"
                           class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 transition-colors">
                            <div class="w-9 h-9 rounded-xl bg-slate-50 flex items-center justify-center text-base flex-shrink-0">
                                {{ $item['icon'] }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-slate-800 truncate">{{ $item['title'] }}</p>
                                <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ $item['body'] }}</p>
                                <p class="text-[11px] text-slate-400 mt-1">{{ $item['time'] }}</p>
                            </div>
                        </a>
                        @empty
                        <div class="px-4 py-10 text-center">
                            <div class="text-2xl mb-2">✨</div>
                            <p class="text-sm font-medium text-slate-600">You're all caught up</p>
                            <p class="text-xs text-slate-400 mt-1">No pending notifications</p>
                        </div>
                        @endforelse
                    </div>

                    <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/70">
                        <a href="{{ route('admin.logs.index') }}"
                           class="block text-center text-xs font-medium text-slate-600 hover:text-violet-600">
                            Open activity logs →
                        </a>
                    </div>
                </div>
            </div>

            <div class="relative" id="admin-user-menu">
                <button type="button" id="admin-user-menu-btn"
                        class="flex items-center gap-3 bg-white border rounded-2xl px-4 py-2.5 hover:shadow-sm">
                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center font-bold text-orange-600">
                        {{ strtoupper(substr($adminUser->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="text-left hidden md:block">
                        <p class="text-sm font-semibold">{{ $adminUser->name ?? 'Administrator' }}</p>
                        <p class="text-xs text-slate-400">Super Admin</p>
                    </div>
                </button>

                <div id="admin-user-menu-dropdown"
                     class="hidden absolute right-0 mt-3 w-64 bg-white rounded-2xl border shadow-xl z-50 overflow-hidden">
                    <div class="p-2">
                        <a href="{{ route('admin.profile.show') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 text-sm text-slate-700">
                            👤 My Profile
                        </a>
                        <a href="{{ route('admin.profile.password') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 text-sm text-slate-700">
                            🔐 Change Password
                        </a>
                        <a href="{{ route('admin.settings.index') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 text-sm text-slate-700">
                            ⚙️ Settings
                        </a>
                        <form action="{{ route('admin.logout') }}" method="POST" class="pt-2 border-t mt-2">
                            @csrf
                            <button type="submit"
                                    class="w-full text-left flex items-center gap-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 text-sm">
                                🚪 Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
(function () {
    function setupMenu(btnId, dropdownId, wrapperId, onOpen) {
        const btn = document.getElementById(btnId);
        const menu = document.getElementById(dropdownId);
        const wrap = document.getElementById(wrapperId);
        if (!btn || !menu || !wrap) return;

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const willOpen = menu.classList.contains('hidden');
            document.querySelectorAll('#admin-notif-dropdown, #admin-user-menu-dropdown').forEach(el => el.classList.add('hidden'));
            if (willOpen) {
                menu.classList.remove('hidden');
                if (typeof onOpen === 'function') onOpen();
            }
        });

        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) menu.classList.add('hidden');
        });
    }

    setupMenu('admin-user-menu-btn', 'admin-user-menu-dropdown', 'admin-user-menu');
    setupMenu('admin-notif-btn', 'admin-notif-dropdown', 'admin-notif-menu', function () {
        fetch('{{ route('admin.notifications.seen') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        }).catch(() => {});
    });
})();
</script>
