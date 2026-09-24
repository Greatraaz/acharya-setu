@extends('admin.layouts.app')
@section('title', 'Request #'.$item->id)
@section('heading', $item->typeLabel().' · #'.$item->id)

@php
    $statusClass = match ($item->status) {
        'completed' => 'bg-green-50 text-green-700 ring-green-200',
        'submitted' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'pending_payment' => 'bg-red-50 text-red-700 ring-red-200',
        default => 'bg-gray-100 text-gray-600 ring-gray-200',
    };
    $isResume = $item->type === 'resume';
@endphp

@section('content')
<div class="space-y-5 max-w-5xl">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.career-services.index') }}"
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
        {{-- Left: mentee submission --}}
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
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Service</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $item->typeLabel() }}</div>
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
                                        default => ucfirst((string) ($item->payment_method ?: 'Pending')),
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
                        @if($item->invoice)
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <span class="text-xs font-mono text-gray-500">{{ $item->invoice->invoice_number }}</span>
                            <a href="{{ route('admin.career-service-invoices.download', $item->invoice) }}"
                               class="text-xs font-semibold text-indigo-600 hover:underline">Download invoice</a>
                        </div>
                        @elseif(in_array($item->payment_status, ['paid', 'free'], true))
                        <form method="POST" action="{{ route('admin.career-service-invoices.generate', $item) }}" class="mt-2">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-indigo-600 hover:underline">Generate invoice</button>
                        </form>
                        @endif
                    </div>
                    <div class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3 sm:col-span-2">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Submitted</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $item->created_at?->format('d M Y, h:i A') }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900">What the mentee submitted</h3>

                @if($item->resumeUrl())
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-slate-50 px-4 py-3.5">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-gray-900">Mentee resume</div>
                        <div class="text-xs text-gray-500">Original file uploaded by mentee</div>
                    </div>
                    <a href="{{ $item->resumeUrl() }}" target="_blank"
                       class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:border-blue-300 hover:bg-blue-50 text-blue-700 text-sm font-medium px-3.5 py-2 rounded-lg transition">
                        View resume
                    </a>
                </div>
                @endif

                @if($item->linkedin_url)
                <div class="rounded-xl border border-gray-200 bg-slate-50 px-4 py-3.5 space-y-2">
                    <div class="text-sm font-semibold text-gray-900">LinkedIn profile</div>
                    <a href="{{ $item->linkedin_url }}" target="_blank" rel="noopener"
                       class="text-sm text-blue-600 hover:text-blue-800 break-all">{{ $item->linkedin_url }}</a>
                    <div>
                        <a href="{{ $item->linkedin_url }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:border-blue-300 hover:bg-blue-50 text-blue-700 text-sm font-medium px-3.5 py-2 rounded-lg transition">
                            Open profile
                        </a>
                    </div>
                </div>
                @endif

                <div class="rounded-xl border border-gray-200 px-4 py-3.5">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-2">Mentee notes</div>
                    @if($item->mentee_notes)
                    <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $item->mentee_notes }}</p>
                    @else
                    <p class="text-sm text-gray-400">No notes provided.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: admin action --}}
        <div class="lg:col-span-2 space-y-5">
            @if($item->status === 'completed')
            <div class="bg-white border border-green-200 rounded-2xl p-5 space-y-4 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="inline-flex w-8 h-8 rounded-full bg-green-100 text-green-700 items-center justify-center text-sm font-bold">✓</span>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Deliverable sent</h3>
                        <p class="text-xs text-gray-500">Visible on mentee dashboard & app</p>
                    </div>
                </div>

                @if($item->admin_notes)
                <div class="rounded-xl bg-green-50/70 border border-green-100 px-4 py-3">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-green-700/70 mb-1">Your notes</div>
                    <p class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed">{{ $item->admin_notes }}</p>
                </div>
                @endif

                @if($item->deliverableUrl())
                <a href="{{ $item->deliverableUrl() }}" target="_blank"
                   class="inline-flex w-full items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition">
                    View deliverable
                </a>
                @endif

                <p class="text-xs text-gray-500">
                    Completed {{ $item->completed_at?->format('d M Y, h:i A') }}
                    @if($item->reviewer) · by {{ $item->reviewer->name }} @endif
                </p>
            </div>
            @elseif($item->status === 'submitted')
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-1">Send review to mentee</h3>
                <p class="text-sm text-gray-500 mb-4 leading-relaxed">
                    @if($isResume)
                        Upload the updated resume. Optional notes will show with the download on their dashboard.
                    @else
                        Upload a document with LinkedIn optimisation guidance. Optional notes will show with the download.
                    @endif
                </p>
                <form method="POST" action="{{ route('admin.career-services.complete', $item) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            {{ $isResume ? 'Updated resume (PDF/DOC)' : 'Optimisation document (PDF/DOC)' }}
                        </label>
                        <input type="file" name="deliverable" accept=".pdf,.doc,.docx" required
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:text-xs file:font-semibold">
                        @error('deliverable') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Suggestions / notes (optional)</label>
                        <textarea name="admin_notes" rows="5"
                                  class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                  placeholder="Short summary the mentee will see…">{{ old('admin_notes') }}</textarea>
                    </div>
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition">
                        Submit to mentee
                    </button>
                </form>
            </div>
            @else
            <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-xl leading-relaxed">
                This request is <strong>{{ strtolower($item->statusLabel()) }}</strong>. Wait until payment is complete before reviewing.
            </div>
            @endif

            <div class="bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-xs text-slate-500 leading-relaxed">
                Tip: review the mentee’s original file first, then upload your deliverable. They’ll see it immediately under Career Services.
            </div>
        </div>
    </div>
</div>
@endsection
