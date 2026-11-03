@extends('admin.layouts.app')
@section('title', 'Roles & Access')
@section('heading', 'Roles & Access')

@php
    $tab = session('tab', $tab ?? 'roles');
    $isSuper = auth()->user()->isSuperAdmin();
    $activeStaff = $staff->where('is_active', true)->count();
    $superRoles = $roles->where('is_super', true)->count();
    $moduleCatalogCount = count($moduleKeys ?? []);
@endphp

@section('content')
<style>
    .roles-modal { backdrop-filter: blur(6px); }
    .roles-modal-panel { animation: rolesModalIn .2s ease-out; }
    @keyframes rolesModalIn {
        from { opacity: 0; transform: translateY(10px) scale(.985); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .role-module-card:has(input:checked) {
        border-color: #a5b4fc;
        background: linear-gradient(180deg, #eef2ff 0%, #e0e7ff 100%);
        box-shadow: inset 0 0 0 1px rgba(99, 102, 241, .12);
    }
    .role-card, .team-card {
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }
    .role-card:hover, .team-card:hover {
        border-color: #c7d2fe;
        box-shadow: 0 14px 32px rgba(79, 70, 229, .09);
        transform: translateY(-2px);
    }
    .roles-hero {
        background:
            radial-gradient(ellipse 80% 120% at 100% -20%, rgba(99,102,241,.16), transparent 55%),
            radial-gradient(ellipse 60% 80% at 0% 100%, rgba(14,165,233,.08), transparent 50%),
            #fff;
    }
</style>

<div class="space-y-5">

    {{-- Hero --}}
    <div class="roles-hero border border-gray-200 rounded-2xl p-5 sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <div class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-indigo-600 mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    Access control
                </div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-900">Roles & team permissions</h2>
                <p class="text-sm text-gray-500 mt-1.5 max-w-2xl">
                    Create roles, choose which admin modules they can open, then invite teammates. The sidebar and pages follow each person’s role.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                @if($tab === 'team')
                <a href="{{ route('admin.roles.index') }}"
                   class="inline-flex items-center justify-center gap-1.5 text-xs font-medium text-gray-600 border border-gray-200 bg-white hover:bg-gray-50 px-3 py-2.5 rounded-xl transition-colors">
                    Manage roles
                </a>
                <button type="button" data-open-modal="staff-modal"
                        class="inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-sm shadow-indigo-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Add team member
                </button>
                @else
                <a href="{{ route('admin.roles.index', ['tab' => 'team']) }}"
                   class="inline-flex items-center justify-center gap-1.5 text-xs font-medium text-gray-600 border border-gray-200 bg-white hover:bg-gray-50 px-3 py-2.5 rounded-xl transition-colors">
                    View team
                </a>
                <button type="button" data-open-modal="role-modal"
                        class="inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-sm shadow-indigo-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    New role
                </button>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="flex items-start gap-2 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/></svg>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach([
            ['Roles', $roles->count(), 'from-indigo-50 to-white', 'text-indigo-700', 'border-indigo-100', 'bg-indigo-100', 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z'],
            ['Team members', $staff->count(), 'from-slate-50 to-white', 'text-slate-700', 'border-slate-200', 'bg-slate-100', 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
            ['Active logins', $activeStaff, 'from-emerald-50 to-white', 'text-emerald-700', 'border-emerald-100', 'bg-emerald-100', 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Full-access roles', $superRoles, 'from-violet-50 to-white', 'text-violet-700', 'border-violet-100', 'bg-violet-100', 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z'],
        ] as [$label, $count, $bg, $tc, $bc, $iconBg, $icon])
        <div class="bg-gradient-to-br {{ $bg }} border {{ $bc }} rounded-2xl p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-2xl font-bold {{ $tc }} tracking-tight">{{ number_format($count) }}</div>
                    <div class="text-xs font-medium text-gray-500 mt-0.5">{{ $label }}</div>
                </div>
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl {{ $iconBg }} {{ $tc }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                </span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Tabs + search --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex gap-1 bg-gray-100 p-1 rounded-xl w-fit">
                <a href="{{ route('admin.roles.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all {{ $tab === 'roles' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Roles
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $tab === 'roles' ? 'bg-indigo-50 text-indigo-700' : 'bg-white/80 text-gray-500' }}">{{ $roles->count() }}</span>
                </a>
                <a href="{{ route('admin.roles.index', ['tab' => 'team']) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all {{ $tab === 'team' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Team
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $tab === 'team' ? 'bg-indigo-50 text-indigo-700' : 'bg-white/80 text-gray-500' }}">{{ $staff->count() }}</span>
                </a>
            </div>
            <div class="relative w-full lg:max-w-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                @if($tab === 'team')
                <input type="text" id="team-search" placeholder="Search name or email…"
                       class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                @else
                <input type="text" id="role-search" placeholder="Search role or module…"
                       class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                @endif
            </div>
        </div>
    </div>

    @if($tab === 'team')
    {{-- Team grid --}}
    @if($staff->isEmpty())
    <div class="bg-white border border-gray-200 rounded-2xl py-20 px-6 text-center">
        <div class="mx-auto w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600 flex items-center justify-center mb-4 border border-indigo-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 1.054-.787 1.991-1.836 2.11A13.01 13.01 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A2.115 2.115 0 014.2 17.28v-.041m14.796-2.52A6.375 6.375 0 0012 15.75h-1.5a6.375 6.375 0 00-6.396 5.998"/></svg>
        </div>
        <p class="font-semibold text-gray-800 text-lg">No team members yet</p>
        <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">Invite an admin, assign a role, and they’ll only see the modules you allow.</p>
        <button type="button" data-open-modal="staff-modal"
                class="inline-flex mt-5 items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
            + Add team member
        </button>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4" id="team-grid">
        @foreach($staff as $member)
        @php
            $initials = collect(explode(' ', trim($member->name)))
                ->filter()
                ->take(2)
                ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                ->implode('');
            $searchBlob = strtolower($member->name.' '.$member->email.' '.($member->adminRole->name ?? ''));
            $roleName = $member->isSuperAdmin()
                ? 'Super Admin'
                : ($member->adminRole->name ?? 'No role');
            $isYou = (int) $member->id === (int) auth()->id();
        @endphp
        <article class="team-card team-row bg-white border border-gray-200 rounded-2xl overflow-hidden flex flex-col"
                 data-search="{{ e($searchBlob) }}">
            <div class="h-1.5 w-full {{ $member->isSuperAdmin() ? 'bg-violet-500' : ($member->is_active ? 'bg-indigo-500' : 'bg-gray-300') }}"></div>
            <div class="p-5 flex flex-col flex-1">
                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 rounded-2xl {{ $member->isSuperAdmin() ? 'bg-violet-100 text-violet-700' : 'bg-indigo-100 text-indigo-700' }} font-bold text-sm flex items-center justify-center flex-shrink-0">
                        {{ $initials ?: '?' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-semibold text-gray-900 truncate">{{ $member->name }}</h3>
                            @if($isYou)
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-600">You</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 truncate mt-0.5">{{ $member->email }}</p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $member->isSuperAdmin() ? 'bg-violet-50 text-violet-700 border border-violet-100' : 'bg-indigo-50 text-indigo-700 border border-indigo-100' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $member->isSuperAdmin() ? 'bg-violet-500' : 'bg-indigo-500' }}"></span>
                        {{ $roleName }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $member->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $member->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                        {{ $member->is_active ? 'Active' : 'Disabled' }}
                    </span>
                </div>

                <div class="mt-auto pt-4 flex items-center gap-1.5 border-t border-gray-100">
                    <button type="button"
                            class="flex-1 inline-flex items-center justify-center text-xs font-semibold px-2.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors"
                            data-open-modal="staff-modal"
                            data-staff="{{ json_encode(['id' => $member->id, 'name' => $member->name, 'email' => $member->email, 'admin_role_id' => $member->admin_role_id, 'is_active' => (bool) $member->is_active]) }}">
                        Edit
                    </button>
                    @if(! $isYou)
                    <form action="{{ route('admin.roles.users.destroy', $member) }}" method="POST" class="inline"
                          onsubmit="return confirm('Remove {{ addslashes($member->name) }} from admin access?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-medium px-2.5 py-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition-colors">
                            Remove
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </article>
        @endforeach
    </div>
    <div id="team-empty-filter" class="hidden bg-white border border-gray-200 rounded-2xl py-12 text-center text-sm text-gray-400">No members match your search.</div>
    @endif

    @else
    {{-- Roles grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4" id="roles-grid">
        @forelse($roles as $role)
        @php
            $labels = $role->moduleLabels();
            $shown = array_slice($labels, 0, 4);
            $extra = max(0, count($labels) - count($shown));
            $moduleCount = $role->is_super ? $moduleCatalogCount : count($role->modules ?? []);
            $modulePct = $moduleCatalogCount > 0 ? min(100, (int) round(($moduleCount / $moduleCatalogCount) * 100)) : 0;
            $searchBlob = strtolower($role->name.' '.implode(' ', $labels));
            $displayName = \Illuminate\Support\Str::title($role->name);
        @endphp
        <article class="role-card bg-white border border-gray-200 rounded-2xl overflow-hidden flex flex-col"
             data-search="{{ e($searchBlob) }}">
            <div class="h-1.5 w-full {{ $role->is_super ? 'bg-violet-500' : 'bg-indigo-500' }}"></div>
            <div class="p-5 flex flex-col flex-1">
                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 {{ $role->is_super ? 'bg-violet-50 text-violet-700 border border-violet-100' : 'bg-indigo-50 text-indigo-700 border border-indigo-100' }}">
                        @if($role->is_super)
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-semibold text-gray-900 leading-snug">{{ $displayName }}</h3>
                            @if($role->is_super)
                            <span class="shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full bg-violet-50 text-violet-700 border border-violet-100">Full access</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $role->users_count }} {{ \Illuminate\Support\Str::plural('member', $role->users_count) }}
                            · {{ $moduleCount }} {{ \Illuminate\Support\Str::plural('module', $moduleCount) }}
                        </p>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="flex items-center justify-between text-[11px] text-gray-400 mb-1.5">
                        <span>Module coverage</span>
                        <span class="font-semibold text-gray-600">{{ $modulePct }}%</span>
                    </div>
                    <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full {{ $role->is_super ? 'bg-violet-500' : 'bg-indigo-500' }}" style="width: {{ $modulePct }}%;"></div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-1.5 min-h-[3.25rem] content-start">
                    @foreach($shown as $label)
                    <span class="text-[11px] px-2 py-1 rounded-lg bg-gray-50 text-gray-600 border border-gray-100">{{ $label }}</span>
                    @endforeach
                    @if($extra > 0)
                    <span class="text-[11px] px-2 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100 font-semibold">+{{ $extra }} more</span>
                    @endif
                </div>

                <div class="mt-auto pt-4 border-t border-gray-100 flex items-center gap-1.5">
                    <button type="button"
                            class="flex-1 inline-flex items-center justify-center text-xs font-semibold px-2.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors"
                            data-open-modal="role-modal"
                            data-role="{{ json_encode(['id' => $role->id, 'name' => $role->name, 'is_super' => (bool) $role->is_super, 'modules' => $role->modules ?? []]) }}">
                        Edit access
                    </button>
                    @if($role->users_count === 0)
                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                          onsubmit="return confirm('Delete role “{{ addslashes($role->name) }}”?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-medium px-2.5 py-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition-colors">Delete</button>
                    </form>
                    @else
                    <span class="text-[11px] text-gray-400 px-1">{{ optional($role->updated_at)->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
        </article>
        @empty
        <div class="md:col-span-2 xl:col-span-3 bg-white border border-gray-200 rounded-2xl py-20 px-6 text-center">
            <div class="mx-auto w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600 flex items-center justify-center mb-4 border border-indigo-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
            </div>
            <p class="font-semibold text-gray-800 text-lg">No roles yet</p>
            <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">Create a role and choose which admin modules it can open.</p>
            <button type="button" data-open-modal="role-modal"
                    class="inline-flex mt-5 items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                + New role
            </button>
        </div>
        @endforelse
    </div>
    <div id="roles-empty-filter" class="hidden bg-white border border-gray-200 rounded-2xl py-12 text-center text-sm text-gray-400">No roles match your search.</div>
    @endif
</div>

{{-- Role modal --}}
<div id="role-modal" class="roles-modal hidden fixed inset-0 z-[80] bg-slate-900/50 items-center justify-center p-4">
    <div class="roles-modal-panel bg-white rounded-2xl w-full max-w-4xl max-h-[92vh] overflow-hidden shadow-2xl border border-gray-100 flex flex-col">
        <form id="role-form" method="POST" class="flex flex-col min-h-0">
            @csrf
            <input type="hidden" name="_method" id="role-method" value="POST">

            <div class="px-6 sm:px-8 py-5 border-b border-gray-100 flex items-start justify-between gap-3 shrink-0 bg-gradient-to-r from-indigo-50/80 to-white">
                <div class="flex items-start gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    </div>
                    <div>
                        <h3 id="role-modal-title" class="text-lg font-bold text-gray-900">New role</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Name the role and choose every module it can open.</p>
                    </div>
                </div>
                <button type="button" data-close-modal class="w-9 h-9 inline-flex items-center justify-center rounded-xl text-gray-400 hover:text-gray-700 hover:bg-white border border-transparent hover:border-gray-200 transition-colors" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 sm:p-8 space-y-6 overflow-y-auto flex-1">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Role name <span class="text-red-400">*</span></label>
                        <input id="role-name" name="name" required maxlength="80"
                               class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                               placeholder="e.g. Finance manager">
                    </div>
                    <div class="flex items-end">
                        <div class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5">
                            <p class="text-[11px] text-gray-400 font-medium uppercase tracking-wide">Selected</p>
                            <p class="text-sm font-semibold text-gray-800 mt-0.5"><span id="role-selected-count">0</span> / {{ $moduleCatalogCount }} modules</p>
                        </div>
                    </div>
                </div>

                @if($isSuper)
                <label class="flex items-start gap-3 p-4 rounded-2xl border border-violet-200 bg-gradient-to-r from-violet-50 to-white cursor-pointer">
                    <input type="checkbox" name="is_super" id="role-super" value="1" class="mt-0.5 rounded border-gray-300 text-indigo-600">
                    <span>
                        <span class="block text-sm font-semibold text-gray-800">Full access to every module</span>
                        <span class="block text-xs text-gray-500 mt-0.5">Super roles bypass module checks. Use only for trusted owners.</span>
                    </span>
                </label>
                @endif

                <div id="role-modules">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Module access</p>
                            <p class="text-xs text-gray-400 mt-0.5">Grouped by sidebar section. Tick what this role can open.</p>
                        </div>
                        <button type="button" id="role-modules-all" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 px-3 py-2 rounded-xl hover:bg-indigo-50 border border-indigo-100 transition-colors">
                            Select all
                        </button>
                    </div>
                    <div class="space-y-5">
                        @foreach($groups as $group => $modules)
                        <div class="rounded-2xl border border-gray-100 bg-gray-50/50 p-4">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500">{{ $group }}</p>
                                <span class="text-[10px] font-semibold text-gray-400">{{ count($modules) }}</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-2">
                                @foreach($modules as $key => $label)
                                <label class="role-module-card flex items-center gap-2.5 text-sm text-gray-700 bg-white border border-gray-150 border-gray-200 rounded-xl px-3 py-2.5 cursor-pointer hover:border-indigo-300 transition-all">
                                    <input type="checkbox" name="modules[]" value="{{ $key }}" class="role-module rounded border-gray-300 text-indigo-600">
                                    <span class="leading-snug">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="px-6 sm:px-8 py-4 border-t border-gray-100 bg-gray-50/80 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 shrink-0">
                <button type="button" data-close-modal class="px-4 py-2.5 text-sm font-medium text-gray-600 border border-gray-200 bg-white hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm shadow-indigo-200">Save role</button>
            </div>
        </form>
    </div>
</div>

{{-- Staff modal --}}
<div id="staff-modal" class="roles-modal hidden fixed inset-0 z-[80] bg-slate-900/50 items-center justify-center p-4">
    <div class="roles-modal-panel bg-white rounded-2xl w-full max-w-2xl max-h-[92vh] overflow-hidden shadow-2xl border border-gray-100 flex flex-col">
        <form id="staff-form" method="POST" class="flex flex-col min-h-0">
            @csrf
            <input type="hidden" name="_method" id="staff-method" value="POST">

            <div class="px-6 sm:px-8 py-5 border-b border-gray-100 flex items-start justify-between gap-3 shrink-0 bg-gradient-to-r from-indigo-50/80 to-white">
                <div class="flex items-start gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </div>
                    <div>
                        <h3 id="staff-modal-title" class="text-lg font-bold text-gray-900">Add team member</h3>
                        <p class="text-xs text-gray-500 mt-0.5">They’ll sign in at the admin login with the role you assign.</p>
                    </div>
                </div>
                <button type="button" data-close-modal class="w-9 h-9 inline-flex items-center justify-center rounded-xl text-gray-400 hover:text-gray-700 hover:bg-white border border-transparent hover:border-gray-200 transition-colors" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 sm:p-8 space-y-5 overflow-y-auto flex-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-400">*</span></label>
                        <input id="staff-name" name="name" required maxlength="120"
                               class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                               placeholder="Full name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-400">*</span></label>
                        <input id="staff-email" name="email" type="email" required
                               class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                               placeholder="name@company.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Password
                            <span id="staff-password-hint" class="text-gray-400 font-normal">*</span>
                        </label>
                        <input id="staff-password" name="password" type="password" minlength="8"
                               class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                               placeholder="Min. 8 characters">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Role <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <select id="staff-role" name="admin_role_id" required
                                    class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 appearance-none cursor-pointer">
                                <option value="">Select a role</option>
                                @foreach($roles as $role)
                                    @if(! $role->is_super || $isSuper)
                                    <option value="{{ $role->id }}">{{ \Illuminate\Support\Str::title($role->name) }}{{ $role->is_super ? ' (full access)' : '' }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </span>
                        </div>
                        @if($roles->isEmpty())
                        <p class="text-xs text-amber-600 mt-1.5">Create a role first, then add team members.</p>
                        @endif
                    </div>
                </div>

                <label class="flex items-center justify-between gap-3 p-4 rounded-2xl border border-gray-200 bg-gradient-to-r from-gray-50 to-white cursor-pointer">
                    <span>
                        <span class="block text-sm font-semibold text-gray-800">Can log in</span>
                        <span class="block text-xs text-gray-500 mt-0.5">Turn off to keep the account without admin access.</span>
                    </span>
                    <input type="checkbox" name="is_active" id="staff-active" value="1" checked class="rounded border-gray-300 text-indigo-600 w-4 h-4">
                </label>
            </div>

            <div class="px-6 sm:px-8 py-4 border-t border-gray-100 bg-gray-50/80 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 shrink-0">
                <button type="button" data-close-modal class="px-4 py-2.5 text-sm font-medium text-gray-600 border border-gray-200 bg-white hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm shadow-indigo-200">Save member</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const roleStore = @json(route('admin.roles.store'));
    const staffStore = @json(route('admin.roles.users.store'));
    const roleUpdate = @json(url('/admin/roles'));
    const staffUpdate = @json(url('/admin/roles/users'));

    function openModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('hidden');
        el.classList.add('flex');
        document.body.style.overflow = 'hidden';
        updateSelectedCount();
    }
    function closeModal(el) {
        el.classList.add('hidden');
        el.classList.remove('flex');
        if (!document.querySelector('[id$="-modal"].flex')) {
            document.body.style.overflow = '';
        }
    }

    function updateSelectedCount() {
        const countEl = document.getElementById('role-selected-count');
        if (!countEl) return;
        const total = document.querySelectorAll('.role-module').length;
        const superEl = document.getElementById('role-super');
        if (superEl && superEl.checked) {
            countEl.textContent = String(total);
            return;
        }
        countEl.textContent = String(document.querySelectorAll('.role-module:checked').length);
    }

    document.querySelectorAll('[data-close-modal]').forEach((btn) => {
        btn.addEventListener('click', () => closeModal(btn.closest('[id$="-modal"]')));
    });
    document.querySelectorAll('[id$="-modal"]').forEach((modal) => {
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(modal); });
    });
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('[id$="-modal"].flex').forEach((modal) => closeModal(modal));
    });

    document.getElementById('role-super')?.addEventListener('change', function () {
        document.getElementById('role-modules').classList.toggle('hidden', this.checked);
        updateSelectedCount();
    });
    document.getElementById('role-modules-all')?.addEventListener('click', function () {
        const boxes = document.querySelectorAll('.role-module');
        const allOn = Array.from(boxes).every((b) => b.checked);
        boxes.forEach((b) => { b.checked = !allOn; });
        this.textContent = allOn ? 'Select all' : 'Clear all';
        updateSelectedCount();
    });
    document.querySelectorAll('.role-module').forEach((box) => {
        box.addEventListener('change', updateSelectedCount);
    });

    document.querySelectorAll('[data-open-modal]').forEach((btn) => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-open-modal');
            if (id === 'role-modal') fillRole(this.dataset.role ? JSON.parse(this.dataset.role) : null);
            if (id === 'staff-modal') fillStaff(this.dataset.staff ? JSON.parse(this.dataset.staff) : null);
            openModal(id);
        });
    });

    function fillRole(data) {
        const form = document.getElementById('role-form');
        const method = document.getElementById('role-method');
        document.getElementById('role-modal-title').textContent = data ? 'Edit role' : 'New role';
        document.getElementById('role-name').value = data ? data.name : '';
        const superEl = document.getElementById('role-super');
        if (superEl) superEl.checked = !!(data && data.is_super);
        document.querySelectorAll('.role-module').forEach((box) => {
            box.checked = !!(data && (data.modules || []).includes(box.value));
        });
        document.getElementById('role-modules').classList.toggle('hidden', !!(superEl && superEl.checked));
        const allBtn = document.getElementById('role-modules-all');
        if (allBtn) allBtn.textContent = 'Select all';
        if (data) {
            form.action = roleUpdate + '/' + data.id;
            method.value = 'PUT';
        } else {
            form.action = roleStore;
            method.value = 'POST';
        }
        updateSelectedCount();
    }

    function fillStaff(data) {
        const form = document.getElementById('staff-form');
        const method = document.getElementById('staff-method');
        const password = document.getElementById('staff-password');
        document.getElementById('staff-modal-title').textContent = data ? 'Edit team member' : 'Add team member';
        document.getElementById('staff-name').value = data ? data.name : '';
        document.getElementById('staff-email').value = data ? data.email : '';
        document.getElementById('staff-role').value = data && data.admin_role_id ? data.admin_role_id : '';
        document.getElementById('staff-active').checked = data ? !!data.is_active : true;
        password.value = '';
        document.getElementById('staff-password-hint').textContent = data ? '(leave blank to keep)' : '*';
        password.required = !data;
        if (data) {
            form.action = staffUpdate + '/' + data.id;
            method.value = 'PUT';
        } else {
            form.action = staffStore;
            method.value = 'POST';
        }
    }

    function bindFilter(inputId, itemSelector, emptyId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            let visible = 0;
            document.querySelectorAll(itemSelector).forEach((row) => {
                const match = !q || (row.dataset.search || '').includes(q);
                row.classList.toggle('hidden', !match);
                if (match) visible++;
            });
            const empty = document.getElementById(emptyId);
            if (empty) empty.classList.toggle('hidden', visible > 0 || !q);
        });
    }

    bindFilter('team-search', '.team-row', 'team-empty-filter');
    bindFilter('role-search', '.role-card', 'roles-empty-filter');
})();
</script>
@endpush
