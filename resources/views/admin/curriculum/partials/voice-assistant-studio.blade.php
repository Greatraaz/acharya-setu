@php
    $vaIndustries = $industries ?? \App\Support\MentorIndustry::all();
    $vaStoreUrl = $vaStoreUrl ?? route('admin.curriculum.templates.store');
    $vaTitle = $vaTitle ?? 'Create with Adarsh';
    $vaSubtitle = $vaSubtitle ?? 'Speak or type — Adarsh will walk you through each form field';
    $vaExistingNames = $existingTemplateNames ?? collect();
@endphp

<div id="voice-assistant-studio"
     class="va-studio fixed inset-0 z-[95] hidden"
     data-voice-studio-shell
     data-store-url="{{ $vaStoreUrl }}"
     data-industries='@json($vaIndustries)'
     data-existing-names='@json($vaExistingNames)'>
    <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-sm" data-va-backdrop></div>

    <div class="relative h-full w-full flex items-stretch justify-center p-3 sm:p-5 lg:p-6">
        <div class="va-shell w-full max-w-4xl max-h-[min(920px,100%)] flex flex-col overflow-hidden rounded-[28px] border border-white/20 shadow-2xl bg-gradient-to-br from-[#f7f8ff] via-white to-[#f3f0ff]">

            {{-- Header --}}
            <div class="shrink-0 px-4 sm:px-6 py-4 border-b border-indigo-100/80 bg-white/70 backdrop-blur-xl">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="relative shrink-0">
                            <span class="va-avatar flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-lg shadow-indigo-500/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/>
                                </svg>
                            </span>
                            <span class="va-online-dot absolute -right-0.5 -bottom-0.5 h-3 w-3 rounded-full bg-emerald-400 ring-2 ring-white" aria-hidden="true"></span>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-base sm:text-lg font-semibold text-slate-900 truncate">{{ $vaTitle }}</h2>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $vaSubtitle }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" data-open-voice-guide
                                class="hidden sm:inline-flex items-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 hover:bg-amber-100 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 16 16"><path d="M4 2.5v11l9-5.5-9-5.5z"/></svg>
                            AI Video Guide
                        </button>
                        <button type="button" data-va-repeat
                                class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A17.98 17.98 0 013 12c0-2.056.35-4.03.997-5.877.234-.847 1.058-1.354 1.938-1.354H6.75z"/></svg>
                            Repeat
                        </button>
                        <button type="button" data-va-close class="w-9 h-9 inline-flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors" aria-label="Close">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="flex items-center justify-between text-[11px] font-medium text-slate-500 mb-1.5">
                        <span data-va-progress-label>Getting started</span>
                        <span data-va-progress-count>0 / 5</span>
                    </div>
                    <div class="h-2 rounded-full bg-indigo-100 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500 transition-all duration-500" data-va-progress style="width:0%"></div>
                    </div>
                </div>
            </div>

            {{-- Chat --}}
            <div class="flex-1 min-h-0 overflow-y-auto px-4 sm:px-6 py-5 space-y-4" data-va-chat></div>

            {{-- Composer --}}
            <div class="shrink-0 border-t border-indigo-100/80 bg-white/80 backdrop-blur-xl px-4 sm:px-6 py-4">
                <div class="flex flex-wrap gap-2 mb-3" data-va-chips></div>

                <div class="flex items-center gap-2 mb-3">
                    <button type="button" data-va-explain
                            class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/></svg>
                        Explain
                    </button>
                    <button type="button" data-va-skip
                            class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8.688c0-.864.933-1.405 1.683-.977l7.108 4.062a1.125 1.125 0 010 1.953l-7.108 4.062A1.125 1.125 0 013 16.81V8.688zM12.75 8.688c0-.864.933-1.405 1.683-.977l7.108 4.062a1.125 1.125 0 010 1.953l-7.108 4.062a1.125 1.125 0 01-1.683-.977V8.688z"/></svg>
                        Skip
                    </button>
                    <select data-va-lang class="ml-auto rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="en-US" selected>English (simple)</option>
                        <option value="en-IN">English (India)</option>
                        <option value="en-GB">English (UK)</option>
                    </select>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" data-va-mic
                            class="va-mic relative inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-lg shadow-indigo-500/35 hover:scale-[1.03] active:scale-95 transition-transform"
                            aria-label="Hold or tap to speak" aria-pressed="false">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/>
                        </svg>
                    </button>

                    <div class="flex-1 relative">
                        <input type="text" data-va-input
                               placeholder="Speak or type your answer here…"
                               class="w-full rounded-2xl border border-slate-200 bg-white pl-4 pr-14 py-3.5 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all"
                               autocomplete="off">
                        <button type="button" data-va-send
                                class="absolute right-2 top-1/2 -translate-y-1/2 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition-colors"
                                aria-label="Send">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                        </button>
                    </div>
                </div>
                <p class="mt-2 text-[11px] text-slate-400 text-center sm:text-left" data-va-hint>Tap the mic to start, tap again to stop. You can also type and press send.</p>
            </div>
        </div>
    </div>

    <form data-va-form method="POST" action="{{ $vaStoreUrl }}" class="hidden">
        @csrf
        <input type="hidden" name="expertise_field" value="">
        <input type="hidden" name="name" value="">
        <input type="hidden" name="description" value="">
        <input type="hidden" name="expertise_tags" value="">
        <input type="hidden" name="icon" value="💻">
        <input type="hidden" name="color" value="#4f46e5">
        <input type="hidden" name="is_active" value="1">
    </form>
</div>

<style>
    .va-studio .va-mic.is-recording {
        background: linear-gradient(135deg, #dc2626, #e11d48) !important;
        box-shadow: 0 0 0 0 rgba(220, 38, 38, .45);
        animation: vaMicPulse 1.2s ease-out infinite;
    }
    @keyframes vaMicPulse {
        0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, .45); }
        70% { box-shadow: 0 0 0 18px rgba(220, 38, 38, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
    }
    .va-bubble-in {
        animation: vaIn .28s ease-out;
    }
    @keyframes vaIn {
        from { opacity: 0; transform: translateY(10px) scale(.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .va-chip {
        transition: transform .15s ease, background-color .15s ease, border-color .15s ease;
    }
    .va-chip:hover {
        transform: translateY(-1px);
        border-color: #a5b4fc;
        background: #eef2ff;
    }
</style>
