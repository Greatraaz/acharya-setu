@extends('admin.layouts.app')
@section('title', 'Deleted ' . ucfirst($type) . 's')
@section('heading', 'Deleted ' . ucfirst($type) . 's')
@section('content')

<div class="space-y-5">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ $type === 'mentee' ? route('admin.mentees.index') : route('admin.mentors.index') }}"
               class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to {{ ucfirst($type) }}s
            </a>
            <span class="text-gray-300">/</span>
            <span class="text-sm text-gray-600 font-medium">Deleted Records</span>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        ✓ {{ session('success') }}
    </div>
    @endif

    {{-- Search --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-4">
        <form method="GET" class="flex gap-3">
            <div class="flex-1 relative">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search deleted {{ $type }}s…"
                       class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all">
            </div>
            <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">Search</button>
        </form>
    </div>

    {{-- Trashed table --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="flex items-center gap-2 px-5 py-3.5 border-b border-gray-100 bg-red-50">
            <div class="w-2 h-2 bg-red-400 rounded-full"></div>
            <h3 class="text-sm font-semibold text-red-700">Deleted {{ ucfirst($type) }}s</h3>
            <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full ml-auto">{{ $users->total() }} records</span>
        </div>

        @if($users->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <div class="text-4xl mb-3">🗑️</div>
            <p class="text-sm font-medium">No deleted {{ $type }}s</p>
            <p class="text-xs mt-1">Deleted users will appear here and can be restored.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-left">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Details</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Deleted</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($users as $user)
                    <tr class="hover:bg-red-50/30 transition-colors group opacity-75 hover:opacity-100">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gray-200 text-gray-500 font-bold text-sm flex items-center justify-center flex-shrink-0 opacity-60">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-600 line-through">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            @if($type === 'mentor')
                            <div class="text-xs text-gray-500">{{ $user->designation ?? '—' }}</div>
                            <div class="text-xs text-gray-400">{{ $user->mentor_status ? ucfirst($user->mentor_status) : '' }}</div>
                            @else
                            <div class="text-xs text-gray-500">{{ $user->college ?? '—' }}</div>
                            <div class="text-xs text-gray-400">{{ $user->field ?? '' }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-xs text-red-500 font-medium">{{ $user->deleted_at->format('d M Y') }}</div>
                            <div class="text-[10px] text-gray-400">{{ $user->deleted_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-xs text-gray-500">{{ $user->created_at->format('d M Y') }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-1">
                                <form method="POST" action="{{ route('admin.users.restore', $user->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs font-semibold px-3 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 border border-green-200 rounded-lg transition-colors">
                                        ↩ Restore
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.force-delete', $user->id) }}"
                                      onsubmit="return confirm('PERMANENTLY delete {{ addslashes($user->name) }}? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="text-xs font-medium px-3 py-1.5 bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 rounded-lg transition-colors">
                                        Permanently Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between px-5 py-3.5 border-t border-gray-100">
            <div class="text-xs text-gray-500">{{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}</div>
            {{ $users->links() }}
        </div>
        @endif
    </div>

    {{-- Info box --}}
    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 flex items-start gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/></svg>
        <div class="text-xs text-amber-700 leading-relaxed">
            <strong>Restore</strong> brings the account back with all data intact.
            <strong>Permanently Delete</strong> removes all data and cannot be undone.
            Deleted users cannot log in until restored.
        </div>
    </div>
</div>

@endsection