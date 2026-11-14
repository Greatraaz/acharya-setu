@php
    $guideVideoUrl = asset('admin/media/voice-assistant-guide.mp4');
    $hasGuideVideo = is_file(public_path('admin/media/voice-assistant-guide.mp4'));
@endphp

{{-- Voice Assistant guide --}}
<div id="voice-assistant-guide-modal"
     class="tpl-modal hidden fixed inset-0 z-[100] bg-slate-900/55 items-center justify-center p-3 sm:p-4"
     data-voice-guide-modal
     data-has-video="{{ $hasGuideVideo ? '1' : '0' }}"
     data-storage-key="vedrix_voice_guide_skip_v1">
    <div class="tpl-modal-panel bg-white rounded-2xl w-full max-w-3xl overflow-hidden shadow-2xl border border-gray-100 flex flex-col max-h-[94vh]">
        {{-- Header --}}
        <div class="px-5 sm:px-6 py-4 border-b border-gray-100 flex items-start justify-between gap-3 shrink-0 bg-white">
            <div class="min-w-0 flex items-start gap-3">
                <span class="relative inline-flex items-center justify-center w-11 h-11 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-md shadow-indigo-500/25 shrink-0">
                    <span class="text-sm font-bold tracking-wide">AD</span>
                </span>
                <div class="min-w-0">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">Voice walkthrough with Adarsh</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Full tour of template, months, weeks, tasks, and MCQ forms — with real field previews</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <label class="hidden sm:flex items-center gap-1.5 text-xs text-gray-500">
                    <span class="sr-only">Voice accent</span>
                    <select data-guide-lang class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="en-US" selected>English (simple)</option>
                        <option value="en-IN">English (India)</option>
                        <option value="en-GB">English (UK)</option>
                    </select>
                </label>
                <button type="button" data-close-modal class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Player shell --}}
        <div class="relative bg-[#0b1220] flex flex-col min-h-[460px] sm:min-h-[540px] max-h-[68vh]">
            @if($hasGuideVideo)
                <div class="relative flex-1 min-h-0 bg-black">
                    <video data-guide-video class="absolute inset-0 w-full h-full object-contain" playsinline preload="metadata"
                           src="{{ $guideVideoUrl }}"></video>
                </div>
            @else
                <div data-guide-stage class="flex-1 min-h-0 overflow-y-auto px-4 sm:px-6 pt-4 pb-3">
                    <div class="mx-auto w-full max-w-xl space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 text-emerald-300 text-[11px] font-semibold px-2.5 py-1 border border-emerald-400/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400" data-guide-live-dot></span>
                                Adarsh explaining
                            </span>
                            <span class="text-[11px] text-slate-400" data-guide-step-pill>Step 1</span>
                        </div>

                        <div class="rounded-2xl border border-white/15 bg-[#121a2b] p-4 sm:p-5 shadow-xl">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="inline-flex w-11 h-11 rounded-full bg-gradient-to-br from-indigo-400 to-violet-500 text-white items-center justify-center text-sm font-bold shadow-lg shadow-indigo-500/20">
                                    AD
                                </span>
                                <div>
                                    <p class="text-base font-semibold text-white leading-snug" data-guide-title>Welcome</p>
                                    <p class="text-xs text-indigo-200 mt-0.5" data-guide-kicker>Adarsh · Curriculum guide</p>
                                </div>
                            </div>
                                <p class="text-sm sm:text-[15px] text-slate-100 leading-relaxed" data-guide-body>
                                    Hello! I will walk you through template, months, weeks, tasks, and MCQs — with the same form fields you use manually.
                                </p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-[#0b1220]/70 p-2 sm:p-3" data-guide-fields-wrap>
                            <div class="flex items-center justify-between gap-2 mb-2 px-1">
                                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-300">Live form UI</p>
                                <p class="text-[11px] font-medium text-indigo-300" data-guide-field-note>Follow the highlighted field</p>
                            </div>
                            <div data-guide-fields></div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Caption strip (separate, always readable) --}}
            <div class="shrink-0 border-t border-white/10 bg-[#070b14] px-4 sm:px-5 py-3">
                <p class="text-sm sm:text-[15px] text-white font-medium leading-relaxed text-center" data-guide-caption>
                    Hello. I am Adarsh. Let me show you each form field, slowly and clearly.
                </p>
            </div>

            {{-- YouTube-style controls --}}
            <div class="shrink-0 border-t border-white/10 bg-[#050814] px-3 sm:px-4 py-3">
                <div class="va-seek relative h-3 mb-3 cursor-pointer rounded-full bg-white/20"
                     data-guide-seek
                     role="slider"
                     aria-label="Seek guide"
                     aria-valuemin="0"
                     aria-valuemax="100"
                     aria-valuenow="0"
                     tabindex="0">
                    <div class="absolute inset-y-0 left-0 rounded-full bg-indigo-500" data-guide-progress style="width:0%"></div>
                    <div class="absolute top-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-white shadow-md border-2 border-indigo-400 pointer-events-none"
                         data-guide-thumb style="left:0%"></div>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-1.5">
                        <button type="button" data-guide-back class="inline-flex items-center justify-center w-9 h-9 rounded-full text-white hover:bg-white/10 transition-colors" aria-label="Back 10 seconds" title="Back">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/>
                            </svg>
                        </button>
                        <button type="button" data-guide-play class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white text-slate-900 hover:bg-indigo-50 transition-colors" aria-label="Play or pause">
                            <svg data-guide-play-icon xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16"><path d="M4 2.5v11l9-5.5-9-5.5z"/></svg>
                            <svg data-guide-pause-icon xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 hidden" fill="currentColor" viewBox="0 0 16 16"><path d="M5 2h2.5v12H5V2zm3.5 0H11v12H8.5V2z"/></svg>
                        </button>
                        <button type="button" data-guide-forward class="inline-flex items-center justify-center w-9 h-9 rounded-full text-white hover:bg-white/10 transition-colors" aria-label="Forward 10 seconds" title="Forward">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l6-6m0 0l-6-6m6 6H9a6 6 0 000 12h3"/>
                            </svg>
                        </button>
                        <button type="button" data-guide-replay class="inline-flex items-center justify-center w-9 h-9 rounded-full text-white/90 hover:bg-white/10 transition-colors" aria-label="Replay from start" title="Replay">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg>
                        </button>
                    </div>
                    <p class="text-xs sm:text-sm font-semibold text-white tabular-nums tracking-wide" data-guide-time>0:00 / 0:00</p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 sm:px-6 py-4 border-t border-gray-100 bg-gray-50/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shrink-0">
            <label class="inline-flex items-center gap-2 text-xs text-gray-600 cursor-pointer select-none">
                <input type="checkbox" data-guide-skip-next class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                Do not open this guide automatically next time
            </label>
            <div class="flex items-center justify-end gap-2">
                <button type="button" data-close-modal class="px-4 py-2.5 text-sm font-medium text-gray-600 border border-gray-200 bg-white hover:bg-gray-50 rounded-xl transition-colors">
                    Close
                </button>
                <button type="button" data-guide-start class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors">
                    Start with Adarsh
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .va-form-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: .9rem 1rem 1rem;
        color: #111827;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .18);
    }
    .va-form-card__head {
        display: flex;
        align-items: center;
        gap: .55rem;
        margin-bottom: .75rem;
    }
    .va-form-card__badge {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #4f46e5;
        background: #eef2ff;
        border: 1px solid #c7d2fe;
        border-radius: 999px;
        padding: .2rem .55rem;
    }
    .va-form-card__head strong {
        font-size: 13px;
        color: #111827;
    }
    .va-form-card__voice {
        display: flex;
        align-items: center;
        gap: .65rem;
        padding: .65rem .75rem;
        margin-bottom: .85rem;
        border-radius: 12px;
        border: 1px solid #e0e7ff;
        background: linear-gradient(135deg, #eef2ff, #fff);
    }
    .va-form-card__mic {
        width: 32px;
        height: 32px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #4f46e5;
        color: #fff;
        flex-shrink: 0;
    }
    .va-form-card__voice-title {
        font-size: 12px;
        font-weight: 700;
        color: #312e81;
        line-height: 1.2;
    }
    .va-form-card__voice-hint {
        font-size: 11px;
        color: #6366f1;
        margin-top: 1px;
    }
    .va-form-card__guide-btn {
        margin-left: auto;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
        background: #4f46e5;
        border-radius: 8px;
        padding: .4rem .65rem;
        white-space: nowrap;
    }
    .va-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .75rem;
    }
    @media (max-width: 640px) {
        .va-form-grid { grid-template-columns: 1fr; }
    }
    .va-field {
        border: 1px solid #e5e7eb;
        background: #fff;
        border-radius: 12px;
        padding: .65rem .7rem;
        transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }
    .va-field.is-active {
        border-color: #818cf8;
        background: #eef2ff;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .18);
    }
    .va-field__label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        margin-bottom: .35rem;
    }
    .va-field__label span { color: #f87171; }
    .va-field.is-active .va-field__label { color: #312e81; }
    .va-field__control { position: relative; }
    .va-field__input {
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: .55rem .75rem;
        font-size: 13px;
        color: #111827;
        background: #fff;
        outline: none;
    }
    .va-field.has-mic .va-field__input { padding-right: 2.4rem; }
    .va-field__input.is-center { text-align: center; font-size: 1.25rem; }
    .va-field__input.is-mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; white-space: pre-wrap; }
    .va-field.is-active .va-field__input {
        border-color: #a5b4fc;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, .12);
    }
    .va-field__mic {
        position: absolute;
        right: .45rem;
        top: .4rem;
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eef2ff;
        color: #4338ca;
        border: 1px solid #c7d2fe;
    }
    .va-field.is-active .va-field__mic {
        background: #4f46e5;
        color: #fff;
        border-color: #6366f1;
    }
    .va-field__hint {
        margin-top: .3rem;
        font-size: 11px;
        color: #9ca3af;
    }
    .va-field__color {
        display: flex;
        align-items: center;
        gap: .6rem;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: .45rem .65rem;
        background: #fff;
    }
    .va-field__swatch {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }
    .va-field__color-text {
        font-size: 12px;
        color: #4b5563;
        font-weight: 600;
    }
    .va-field__toggle {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        font-size: 12px;
        color: #4b5563;
    }
    .va-field__toggle-knob {
        width: 36px;
        height: 20px;
        border-radius: 999px;
        background: #d1d5db;
        position: relative;
    }
    .va-field__toggle-knob::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 16px;
        height: 16px;
        border-radius: 999px;
        background: #fff;
        transition: left .15s ease;
    }
    .va-field__toggle.is-on .va-field__toggle-knob { background: #4f46e5; }
    .va-field__toggle.is-on .va-field__toggle-knob::after { left: 18px; }
    .va-field[data-field-key="description"],
    .va-field[data-field-key="learning_outcomes"],
    .va-field[data-field-key="mentor_guide"],
    .va-field[data-field-key="expertise_tags"],
    .va-field[data-field-key="question"],
    .va-field[data-field-key="explanation"],
    .va-field[data-field-key="is_active"],
    .va-field[data-field-key="is_required"] {
        grid-column: 1 / -1;
    }
    .va-seek:hover { height: .85rem; }
    .va-seek:hover [data-guide-thumb] { transform: translateY(-50%) scale(1.08); }
</style>
