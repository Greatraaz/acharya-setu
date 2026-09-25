@extends('admin.layouts.app')
@section('title', 'Mock interview #'.$item->id)
@section('heading', 'Mock interview · #'.$item->id)

@php
    $statusClass = match ($item->status) {
        'completed' => 'bg-green-50 text-green-700 ring-green-200',
        'confirmed' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'submitted' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'cancelled' => 'bg-gray-100 text-gray-600 ring-gray-200',
        default => 'bg-gray-100 text-gray-600 ring-gray-200',
    };
    $tz = $item->timezone ?: 'Asia/Kolkata';
@endphp

@section('content')
<div class="space-y-5 max-w-5xl">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.mock-interviews.index') }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-800">
            ← Back to list
        </a>
        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold ring-1 ring-inset {{ $statusClass }}">
            {{ $item->statusLabel() }}
        </span>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl flex items-center gap-2">
        <span class="font-bold">✓</span> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">{{ session('error') }}</div>
    @endif

    <div class="grid lg:grid-cols-5 gap-5 items-start">
        <div class="lg:col-span-3 space-y-5">
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg font-bold">
                        {{ strtoupper(substr($item->user->name ?? 'M', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-base font-semibold text-gray-900 truncate">{{ $item->user->name ?? '—' }}</div>
                        <div class="text-sm text-gray-500 truncate">{{ $item->user->email ?? '' }}</div>
                    </div>
                </div>

                <div class="p-5 grid sm:grid-cols-2 gap-4">
                    <div class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Preferred time</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $item->preferred_at?->timezone($tz)->format('d M Y, h:i A') }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ $tz }} · {{ $item->duration_minutes }} minutes</div>
                    </div>
                    <div class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Payment</div>
                        <div class="text-sm font-semibold text-gray-900">
                            @if($item->is_paid_addon)
                                @php
                                    $methodLabel = match ($item->payment_method) {
                                        'wallet' => 'Wallet',
                                        'hybrid' => 'Wallet + Razorpay',
                                        'razorpay' => 'Razorpay',
                                        default => ucfirst((string) ($item->payment_method ?: 'Paid')),
                                    };
                                @endphp
                                Paid add-on · ₹{{ number_format((float) $item->amount, 0) }}
                                <span class="text-xs font-medium text-gray-500">({{ $item->payment_status }} · {{ $methodLabel }})</span>
                                @if((float) ($item->wallet_amount ?? 0) > 0 || (float) ($item->razorpay_amount ?? 0) > 0)
                                <div class="text-xs text-gray-500 mt-1">
                                    @if((float) ($item->wallet_amount ?? 0) > 0) Wallet ₹{{ number_format((float) $item->wallet_amount, 0) }} @endif
                                    @if((float) ($item->wallet_amount ?? 0) > 0 && (float) ($item->razorpay_amount ?? 0) > 0) · @endif
                                    @if((float) ($item->razorpay_amount ?? 0) > 0) Razorpay ₹{{ number_format((float) $item->razorpay_amount, 0) }} @endif
                                </div>
                                @endif
                            @else
                                Included in plan
                                @if($item->plan_slug)
                                    <span class="ml-1 inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700">{{ ucfirst($item->plan_slug) }}</span>
                                @endif
                            @endif
                        </div>
                    </div>
                    @if($item->target_role)
                    <div class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3 sm:col-span-2">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Target role</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $item->target_role }}</div>
                    </div>
                    @endif
                    <div class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3 sm:col-span-2">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Submitted</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $item->created_at?->format('d M Y, h:i A') }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900">Mentee request</h3>

                <div class="rounded-xl border border-gray-200 px-4 py-3.5">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-2">Notes</div>
                    @if($item->mentee_notes)
                    <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $item->mentee_notes }}</p>
                    @else
                    <p class="text-sm text-gray-400">No notes provided.</p>
                    @endif
                </div>

                @if($item->mentor)
                <div class="rounded-xl border border-gray-200 bg-slate-50 px-4 py-3.5">
                    <div class="text-sm font-semibold text-gray-900">Assigned mentor</div>
                    <div class="text-sm text-gray-700 mt-1">{{ $item->mentor->name }} · {{ $item->mentor->email }}</div>
                    @if($item->confirmed_at)
                    <div class="text-xs text-gray-500 mt-2">Confirmed {{ $item->confirmed_at->format('d M Y, h:i A') }}</div>
                    @endif
                </div>
                @endif

                @if($item->status === 'completed' && $item->feedback)
                <div class="rounded-xl border border-green-200 bg-green-50/50 px-4 py-3.5">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-green-700/70 mb-2">Feedback sent to mentee</div>
                    <p class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed">{{ $item->feedback }}</p>
                    <p class="text-xs text-gray-500 mt-3">
                        Completed {{ $item->completed_at?->format('d M Y, h:i A') }}
                        @if($item->reviewer) · by {{ $item->reviewer->name }} @endif
                    </p>
                </div>
                @endif
            </div>
        </div>

        <div class="lg:col-span-2 space-y-5">
            @if($item->status === 'submitted')
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-1">Confirm booking</h3>
                <p class="text-sm text-gray-500 mb-4 leading-relaxed">Assign a mentor and confirm the slot. Optional notes are visible to the mentee.</p>
                <form method="POST" action="{{ route('admin.mock-interviews.confirm', $item) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Mentor <span class="text-red-500">*</span></label>
                        <select name="mentor_id" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            <option value="">— Select mentor —</option>
                            @foreach($mentors as $mentor)
                            <option value="{{ $mentor->id }}" @selected((int) old('mentor_id', $item->mentor_id) === (int) $mentor->id)>{{ $mentor->name }} ({{ $mentor->email }})</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Required — creates an Agora channel so mentee and mentor can join the call.</p>
                        @error('mentor_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes for mentee (optional)</label>
                        <textarea name="admin_notes" rows="3"
                                  class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                  placeholder="Session link, prep tips…">{{ old('admin_notes', $item->admin_notes) }}</textarea>
                    </div>
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition">
                        Confirm mock interview
                    </button>
                </form>
            </div>
            @endif

            @if(in_array($item->status, ['submitted', 'confirmed'], true))
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-1">Mark complete</h3>
                <p class="text-sm text-gray-500 mb-4 leading-relaxed">Share interview feedback with the mentee. This marks the booking as completed.</p>
                <form method="POST" action="{{ route('admin.mock-interviews.complete', $item) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Feedback <span class="text-red-600">*</span></label>
                        <textarea name="feedback" rows="6" required
                                  class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                  placeholder="Strengths, areas to improve, recommended next steps…">{{ old('feedback', $item->feedback) }}</textarea>
                        @error('feedback') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Admin notes (optional)</label>
                        <textarea name="admin_notes" rows="3"
                                  class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                  placeholder="Internal or mentee-visible notes…">{{ old('admin_notes', $item->admin_notes) }}</textarea>
                    </div>
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition">
                        Complete & send feedback
                    </button>
                </form>
            </div>
            @endif

            @if(! in_array($item->status, ['completed', 'cancelled'], true))
            <div class="bg-white border border-red-100 rounded-2xl p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-1">Cancel request</h3>
                <p class="text-sm text-gray-500 mb-4">The mentee will see this booking as cancelled.</p>
                <form method="POST" action="{{ route('admin.mock-interviews.cancel', $item) }}" class="space-y-4"
                      onsubmit="return confirm('Cancel this mock interview request?');">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Reason / notes (optional)</label>
                        <textarea name="admin_notes" rows="3"
                                  class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                  placeholder="Why this was cancelled…">{{ old('admin_notes') }}</textarea>
                    </div>
                    <button type="submit"
                            class="w-full border border-red-200 text-red-700 hover:bg-red-50 text-sm font-medium px-5 py-2.5 rounded-xl transition">
                        Cancel booking
                    </button>
                </form>
            </div>
            @endif

            @if($item->status === 'completed')
            <div class="bg-white border border-green-200 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="inline-flex w-8 h-8 rounded-full bg-green-100 text-green-700 items-center justify-center text-sm font-bold">✓</span>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Completed</h3>
                        <p class="text-xs text-gray-500">Feedback is visible on the mentee dashboard</p>
                    </div>
                </div>
                @if($item->admin_notes)
                <div class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3 mt-4">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Admin notes</div>
                    <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $item->admin_notes }}</p>
                </div>
                @endif
            </div>
            @endif

            <div class="bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-xs text-slate-500 leading-relaxed">
                Tip: confirm the slot and assign a mentor before the preferred time. After the session, add detailed feedback so the mentee can improve.
            </div>
        </div>
    </div>
</div>
@endsection
