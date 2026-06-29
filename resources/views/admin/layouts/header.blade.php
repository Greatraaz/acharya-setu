<header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-slate-200 px-8 py-5">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">@yield('heading','Dashboard')</h1>
            <p class="text-sm text-slate-500 mt-1">Welcome back, manage your platform efficiently.</p>
        </div>
        <div class="flex items-center gap-4">
            <button class="relative bg-white border rounded-2xl p-3">
                🔔<span
                    class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center"
                    >3</span
                >
            </button>
            <div class="relative group">
                <button class="flex items-center gap-3 bg-white border rounded-2xl px-4 py-2.5 hover:shadow-sm">
                    <div
                        class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center font-bold text-orange-600"
                    >
                        A
                    </div>
                    <div class="text-left hidden md:block">
                        <p class="text-sm font-semibold">Administrator</p>
                        <p class="text-xs text-slate-400">Super Admin</p>
                    </div>
                </button>
                <div
                    class="absolute right-0 mt-3 w-64 bg-white rounded-2xl border shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden"
                >
                    <div class="p-2">
                        <a
                            href="#"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50"
                            >👤 My Profile</a
                        >
                        <a
                            href="#"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50"
                            >🔐 Change Password</a
                        >
                        <a
                            href="#"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50"
                            >⚙️ Settings</a
                        >
                        <form action="{{ route('admin.logout') }}" data-redirect="{{ route('admin.login') }}" method="POST" class="pt-2 border-t mt-2 formsubmit">
                            @csrf
                            <button
                                class="w-full text-left flex items-center gap-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50"
                            >
                                🚪 Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
