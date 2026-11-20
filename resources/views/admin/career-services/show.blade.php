@extends('admin.layouts.app')
@section('title', $item->typeLabel().' #'.$item->id)
@section('heading', 'Career Service Detail')

@php
    $statusMeta = match ($item->status) {
        'completed' => ['bg' => '#ecfdf5', 'text' => '#047857', 'dot' => '#10b981', 'ring' => 'ring-green-200'],
        'submitted' => ['bg' => '#fffbeb', 'text' => '#b45309', 'dot' => '#f59e0b', 'ring' => 'ring-amber-200'],
        'pending_payment' => ['bg' => '#fef2f2', 'text' => '#b91c1c', 'dot' => '#ef4444', 'ring' => 'ring-red-200'],
        default => ['bg' => '#f8fafc', 'text' => '#64748b', 'dot' => '#94a3b8', 'ring' => 'ring-gray-200'],
    };
    $isResume = $item->type === 'resume';
    $methodLabel = match ($item->payment_method) {
        'wallet' => 'Wallet',
        'hybrid' => 'Wallet + Razorpay',
        'razorpay' => 'Razorpay',
        'plan' => 'Plan benefit',
        default => ucfirst((string) ($item->payment_method ?: '—')),
    };
@endphp

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
.admin-cs{ --brand:#f59e0b; --brand-dark:#d97706; font-family:"DM Sans",system-ui,sans-serif; }
.admin-cs h2,.admin-cs h3,.admin-cs .admin-cs-head{ font-family:"Sora",system-ui,sans-serif; }
.admin-cs .admin-cs-hero{
    background:radial-gradient(420px 140px at 100% 0%, rgba(245,158,11,.14), transparent 70%), #fff;
    border:1px solid #e5e7eb; border-radius:16px; box-shadow:0 4px 24px rgba(0,0,0,.06);
}
.admin-cs .admin-cs-accent{ background:linear-gradient(135deg,var(--brand),var(--brand-dark)); }
</style>
<div class="max-w-6xl space-y-5 admin-cs">

    {{-- Top bar --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.career-services.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            All Career Services
        </a>
        <div class="flex flex-wrap items-center gap-2">
            @if($item->invoice)
            <a href="{{ route('admin.career-service-invoices.download', $item->invoice) }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Invoice {{ $item->invoice->invoice_number }}
            </a>
            @elseif(in_array($item->payment_status, ['paid', 'free'], true))
            <form method="POST" action="{{ route('admin.career-service-invoices.generate', $item) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 px-4 py-2 rounded-xl transition-colors bg-white">
                    Generate invoice
                </button>
            </form>
            @endif
        </div>
    </div>

    @foreach(['success','error'] as $flash)
        @if(session($flash))
        <div class="flex items-center gap-3 text-sm px-4 py-3 rounded-xl border
            {{ $flash === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800' }}">
            {{ session($flash) }}
        </div>
        @endif
    @endforeach

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">

        {{-- LEFT --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Overview --}}
            <div class="admin-cs-hero rounded-2xl p-6">
                <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h2 class="text-lg font-extrabold text-gray-900 tracking-tight">{{ $item->typeLabel() }}</h2>
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full"
                                  style="background:{{ $statusMeta['bg'] }};color:{{ $statusMeta['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $statusMeta['dot'] }}"></span>
                                {{ $item->statusLabel() }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full
                                {{ $isResume ? 'bg-violet-50 text-violet-700' : 'bg-sky-50 text-sky-700' }}">
                                {{ $isResume ? 'Resume' : 'LinkedIn' }}
                            </span>
                        </div>
                        <div class="font-mono text-xs text-gray-400">Request #{{ $item->id }}</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl admin-cs-accent text-white flex items-center justify-center text-lg font-bold shadow-md shadow-amber-200/60">
                            {{ strtoupper(substr($item->user->name ?? 'M', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-900 truncate">{{ $item->user->name ?? '—' }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ $item->user->email ?? '' }}</div>
                        </div>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <div class="bg-gray-50 rounded-xl px-4 py-3">
                        <div class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Payment</div>
                        <div class="text-sm font-semibold text-gray-800">
                            @if($item->is_paid_addon)
                                ₹{{ number_format((float) $item->amount, 0) }}
                                <span class="text-xs font-medium text-gray-500">· {{ $item->payment_status }} · {{ $methodLabel }}</span>
                            @else
                                Included in plan
                                @if($item->plan_slug)
                                    <span class="ml-1 inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700">{{ ucfirst($item->plan_slug) }}</span>
                                @endif
                            @endif
                        </div>
                        @if($item->is_paid_addon && ((float) ($item->wallet_amount ?? 0) > 0 || (float) ($item->razorpay_amount ?? 0) > 0))
                        <div class="text-xs text-gray-500 mt-1">
                            @if((float) ($item->wallet_amount ?? 0) > 0) Wallet ₹{{ number_format((float) $item->wallet_amount, 0) }} @endif
                            @if((float) ($item->wallet_amount ?? 0) > 0 && (float) ($item->razorpay_amount ?? 0) > 0) · @endif
                            @if((float) ($item->razorpay_amount ?? 0) > 0) Razorpay ₹{{ number_format((float) $item->razorpay_amount, 0) }} @endif
                        </div>
                        @endif
                        @if($item->invoice)
                        <div class="mt-2 text-xs font-mono text-gray-400">{{ $item->invoice->invoice_number }}</div>
                        @endif
                    </div>

                    <div class="bg-gray-50 rounded-xl px-4 py-3">
                        <div class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Submitted</div>
                        <div class="text-sm font-semibold text-gray-800">{{ $item->created_at?->format('D, d M Y · h:i A') }}</div>
                    </div>

                    @if($item->completed_at)
                    <div class="bg-gray-50 rounded-xl px-4 py-3">
                        <div class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Completed</div>
                        <div class="text-sm font-semibold text-gray-800">{{ $item->completed_at->format('D, d M Y · h:i A') }}</div>
                        @if($item->reviewer)
                        <div class="text-xs text-gray-500 mt-1">by {{ $item->reviewer->name }}</div>
                        @endif
                    </div>
                    @endif

                    <div class="bg-gray-50 rounded-xl px-4 py-3">
                        <div class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Billing</div>
                        <div class="text-sm font-semibold text-gray-800">
                            @if($item->is_paid_addon)
                                Paid add-on
                            @else
                                Plan benefit
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mentee submission --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-sm font-bold text-gray-900">What the mentee submitted</h3>
                </div>

                @if($item->resumeUrl())
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-violet-50/40 px-4 py-3.5">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-violet-100 text-violet-700 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-900">Mentee resume</div>
                            <div class="text-xs text-gray-500">Original upload for review</div>
                        </div>
                    </div>
                    <a href="{{ $item->resumeUrl() }}" target="_blank"
                       class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 text-indigo-700 text-sm font-medium px-3.5 py-2 rounded-xl transition-colors">
                        View resume
                    </a>
                </div>
                @endif

                @if($item->linkedin_url)
                <div class="rounded-xl border border-gray-200 bg-sky-50/40 px-4 py-3.5 space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-semibold text-gray-900 mb-1">LinkedIn profile</div>
                            <a href="{{ $item->linkedin_url }}" target="_blank" rel="noopener"
                               class="text-sm text-indigo-600 hover:text-indigo-800 break-all">{{ $item->linkedin_url }}</a>
                        </div>
                    </div>
                    <a href="{{ $item->linkedin_url }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 text-indigo-700 text-sm font-medium px-3.5 py-2 rounded-xl transition-colors">
                        Open profile
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
                @endif

                <div class="rounded-xl border border-gray-200 px-4 py-3.5">
                    <div class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-2">Mentee notes</div>
                    @if($item->mentee_notes)
                    <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $item->mentee_notes }}</p>
                    @else
                    <p class="text-sm text-gray-400">No notes provided.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT --}}
        <div class="space-y-5">
            @if($item->status === 'completed')
            <div class="bg-white border border-emerald-200 rounded-2xl p-5 space-y-4">
                <div class="flex items-center gap-3">
                    <span class="inline-flex w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Deliverable sent</h3>
                        <p class="text-xs text-gray-500">Visible on mentee dashboard &amp; app</p>
                    </div>
                </div>

                @if($item->admin_notes)
                <div class="rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700/70 mb-1">Your notes</div>
                    <p class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed">{{ $item->admin_notes }}</p>
                </div>
                @endif

                @if($item->deliverableUrl())
                <a href="{{ $item->deliverableUrl() }}" target="_blank"
                   class="inline-flex w-full items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    View deliverable
                </a>
                @endif

                <p class="text-xs text-gray-500">
                    Completed {{ $item->completed_at?->format('d M Y, h:i A') }}
                    @if($item->reviewer) · by {{ $item->reviewer->name }} @endif
                </p>

                @if($item->mentee_rating)
                <div class="rounded-xl bg-amber-50 border border-amber-100 px-4 py-3">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-amber-700/70 mb-1">Mentee rating</div>
                    <div class="text-sm font-bold text-amber-800">★ {{ $item->mentee_rating }}/5</div>
                    @if($item->mentee_review)
                    <p class="text-sm text-gray-700 mt-2 whitespace-pre-wrap">{{ $item->mentee_review }}</p>
                    @endif
                </div>
                @endif
            </div>
            @elseif($item->status === 'submitted')
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Send review to mentee</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        @if($isResume)
                            Upload the updated resume. Optional notes appear with the download.
                        @else
                            Upload LinkedIn optimisation guidance. Optional notes appear with the download.
                        @endif
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.career-services.complete', $item) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            {{ $isResume ? 'Updated resume (PDF/DOC)' : 'Optimisation document (PDF/DOC)' }}
                        </label>
                        <input type="file" name="deliverable" accept=".pdf,.doc,.docx" required
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 file:text-xs file:font-semibold focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none">
                        @error('deliverable') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Suggestions / notes (optional)</label>
                        <textarea name="admin_notes" rows="5"
                                  class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                                  placeholder="Short summary the mentee will see…">{{ old('admin_notes') }}</textarea>
                    </div>
                    <button type="submit"
                            class="w-full bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-colors">
                        Submit to mentee
                    </button>
                </form>
            </div>
            @else
            <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-xl leading-relaxed">
                This request is <strong>{{ strtolower($item->statusLabel()) }}</strong>. Wait until payment is complete before reviewing.
            </div>
            @endif

            <div class="bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3.5">
                <div class="text-xs font-semibold text-slate-600 mb-1">Workflow tip</div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Review the mentee’s original file first, then upload your deliverable. They’ll see it immediately under Career Services.
                </p>
            </div>

            @if($item->user)
            <div class="bg-white border border-gray-200 rounded-2xl p-4">
                <div class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-2">Mentee</div>
                <div class="text-sm font-semibold text-gray-900">{{ $item->user->name }}</div>
                <div class="text-xs text-gray-500 break-all">{{ $item->user->email }}</div>
                <a href="{{ route('admin.mentees.show', $item->user) }}"
                   class="inline-flex mt-3 text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                    View mentee profile →
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
