@extends('admin.layouts.app')
@section('title','Plans')
@section('heading','Subscription Plans')
@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-500">Manage subscription plans shown to your users.</p>
        <a href="{{ route('admin.plans.create') }}"
           class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition-colors w-full sm:w-auto">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add New Plan
        </a>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if($plans->isEmpty())
    <div class="bg-white border border-gray-200 rounded-2xl p-10 sm:p-16 text-center">
        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <p class="text-gray-600 font-medium mb-1">No plans yet</p>
        <p class="text-gray-400 text-sm mb-5">Create your first subscription plan to get started.</p>
        <a href="{{ route('admin.plans.create') }}" class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm font-medium px-5 py-2.5 rounded-xl hover:bg-blue-700 transition">
            Create First Plan
        </a>
    </div>
    @else

    {{-- Summary --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @php $activePlans = $plans->where('is_active', true)->where('deleted_at', null); @endphp
        @foreach([
            ['Total Plans', $planStats['total'] ?? $plans->whereNull('deleted_at')->count(), 'bg-blue-50', 'text-blue-600'],
            ['Active', $planStats['active'] ?? $activePlans->count(), 'bg-green-50', 'text-green-600'],
            ['Featured', $planStats['featured'] ?? $plans->where('is_featured',true)->whereNull('deleted_at')->count(), 'bg-amber-50', 'text-amber-600'],
            ['Archived', $planStats['archived'] ?? $plans->whereNotNull('deleted_at')->count(), 'bg-gray-50', 'text-gray-500'],
        ] as [$label, $count, $bg, $tc])
        <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-4 flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 sm:w-10 sm:h-10 {{ $bg }} rounded-lg flex items-center justify-center flex-shrink-0">
                <span class="text-base sm:text-lg font-bold {{ $tc }}">{{ $count }}</span>
            </div>
            <span class="text-xs sm:text-sm text-gray-600 font-medium truncate">{{ $label }}</span>
        </div>
        @endforeach
    </div>

    {{-- Plans list: cards on mobile, table on md+ --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between px-4 sm:px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">All Plans</h3>
            <span class="text-xs text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full w-fit">{{ $planStats['total'] ?? $plans->total() }} plans</span>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-gray-100">
            @foreach($plans as $plan)
            @php $archived = !is_null($plan->deleted_at); @endphp
            <div class="p-4 space-y-3 {{ $archived ? 'opacity-50' : '' }}">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                         style="background: {{ $plan->color ?: '#2563eb' }};">
                        {{ strtoupper(substr($plan->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-sm font-semibold text-gray-900">{{ $plan->name }}</span>
                            @if($plan->is_featured)
                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-800">Featured</span>
                            @endif
                            @if($archived)
                            <span class="text-[10px] font-medium bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded-md">Archived</span>
                            @elseif($plan->is_active)
                            <span class="text-[10px] font-semibold bg-green-50 text-green-700 px-1.5 py-0.5 rounded-md">Active</span>
                            @else
                            <span class="text-[10px] font-semibold bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded-md">Inactive</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-400 font-mono mt-0.5 truncate">{{ $plan->slug }}</div>
                        <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-600">
                            <span><strong>{{ $plan->formatted_price_monthly }}</strong>/mo</span>
                            <span><strong>{{ $plan->formatted_price_yearly }}</strong>/yr</span>
                            <span>{{ count($plan->features_list) }} features</span>
                            <span class="font-mono text-gray-400">#{{ $plan->sort_order ?: '—' }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if($archived)
                    <form method="POST" action="{{ route('admin.plans.restore', $plan->id) }}">
                        @csrf
                        <button type="submit" class="text-xs text-green-600 font-medium bg-green-50 px-3 py-1.5 rounded-lg">Restore</button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('admin.plans.toggle-status', $plan) }}">
                        @csrf
                        <button type="submit" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ $plan->is_active ? 'text-gray-600 bg-gray-100' : 'text-green-700 bg-green-50' }}">
                            {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <a href="{{ route('admin.plans.edit', $plan) }}"
                       class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 bg-blue-50 px-3 py-1.5 rounded-lg">Edit</a>
                    <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" onsubmit="return confirm('Archive this plan?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1 text-xs font-medium text-red-600 bg-red-50 px-3 py-1.5 rounded-lg">Archive</button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full min-w-[720px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Monthly</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Yearly</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Trial</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Features</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($plans as $plan)
                    @php $archived = !is_null($plan->deleted_at); @endphp
                    <tr class="hover:bg-gray-50 transition-colors {{ $archived ? 'opacity-50' : '' }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                                     style="background: {{ $plan->color ?: '#2563eb' }};">
                                    {{ strtoupper(substr($plan->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-semibold text-gray-900">{{ $plan->name }}</span>
                                        @if($plan->is_featured)
                                        <span class="text-xs font-medium px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-800">Featured</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-400 font-mono mt-0.5 truncate">{{ $plan->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-gray-900">{{ $plan->formatted_price_monthly }}</span>
                            @if($plan->price_monthly > 0)<span class="text-xs text-gray-400">/mo</span>@endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-gray-900">{{ $plan->formatted_price_yearly }}</span>
                            @if($plan->price_yearly > 0)<span class="text-xs text-gray-400">/yr</span>@endif
                            @if($plan->yearly_savings_percent > 0)
                            <div class="text-xs text-green-600 font-medium mt-0.5">Save {{ $plan->yearly_savings_percent }}%</div>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @if($plan->trial_days > 0)
                            <span class="text-xs font-medium bg-purple-50 text-purple-700 px-2 py-1 rounded-lg">{{ $plan->trial_days }}d free</span>
                            @else
                            <span class="text-xs text-gray-400">None</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @php $features = $plan->features_list; @endphp
                            @if(count($features))
                            <div class="text-xs font-semibold text-gray-700">{{ count($features) }} features</div>
                            <div class="text-xs text-gray-400 mt-0.5 truncate max-w-[160px]">{{ $features[0] ?? '' }}</div>
                            @else
                            <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @if($archived)
                            <span class="inline-flex items-center gap-1 text-xs font-medium bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full">Archived</span>
                            @elseif($plan->is_active)
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-green-50 text-green-700 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>Active
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>Inactive
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-xs font-mono text-gray-500 bg-gray-100 px-2 py-0.5 rounded">#{{ $plan->sort_order ?: '—' }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                @if($archived)
                                <form method="POST" action="{{ route('admin.plans.restore', $plan->id) }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="text-xs text-green-600 hover:text-green-700 font-medium bg-green-50 hover:bg-green-100 px-3 py-1.5 rounded-lg transition">Restore</button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('admin.plans.toggle-status', $plan) }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium px-3 py-1.5 rounded-lg transition whitespace-nowrap
                                        {{ $plan->is_active ? 'text-gray-600 bg-gray-100 hover:bg-gray-200' : 'text-green-700 bg-green-50 hover:bg-green-100' }}">
                                        {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.plans.edit', $plan) }}"
                                   class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" class="m-0"
                                      onsubmit="return confirm('Archive this plan?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Archive
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $plans])
    </div>

    {{-- Live Preview --}}
    <div>
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Live Preview — How users see your plans</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 sm:gap-6 pt-2">
            @foreach($plans->where('is_active', true)->whereNull('deleted_at') as $plan)
            @php $planColor = $plan->color ?: '#2563eb'; @endphp
            <div class="relative bg-white border-2 rounded-2xl p-5 sm:p-6 flex flex-col h-full
                {{ $plan->is_featured ? 'border-blue-400 shadow-lg shadow-blue-50' : 'border-gray-200' }}
                {{ $plan->badge_label ? 'pt-8' : '' }}">
                @if($plan->badge_label)
                <div class="absolute inset-x-0 -top-3 flex justify-center px-3 pointer-events-none">
                    <span class="inline-block max-w-full text-[11px] sm:text-xs font-bold px-3 py-1 rounded-full text-white text-center leading-snug shadow-sm"
                          style="background: {{ $planColor }};">
                        {{ $plan->badge_label }}
                    </span>
                </div>
                @endif

                <div class="w-10 h-10 rounded-xl mb-3 flex items-center justify-center font-bold text-lg"
                     style="background: {{ $planColor }}20; color: {{ $planColor }};">
                    {{ strtoupper(substr($plan->name, 0, 1)) }}
                </div>
                <h4 class="font-bold text-gray-900 text-base mb-1">{{ $plan->name }}</h4>
                @if($plan->description)
                <p class="text-xs text-gray-500 mb-3 leading-relaxed">{{ $plan->description }}</p>
                @endif
                <div class="mb-4">
                    <span class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $plan->formatted_price_monthly }}</span>
                    @if($plan->price_monthly > 0)<span class="text-sm text-gray-400">/month</span>@endif
                </div>
                @if(count($plan->features_list))
                <ul class="space-y-2 mt-auto">
                    @foreach(array_slice($plan->features_list, 0, 5) as $feature)
                    <li class="flex items-start gap-2 text-xs text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="{{ $planColor }}" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
                        <span class="break-words">{{ $feature }}</span>
                    </li>
                    @endforeach
                    @if(count($plan->features_list) > 5)
                    <li class="text-xs text-gray-400">+{{ count($plan->features_list) - 5 }} more features</li>
                    @endif
                </ul>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@endsection
