@php
    $voiceTitle = $voiceTitle ?? 'Voice fill';
    $voiceHint = $voiceHint ?? 'Tap mic to speak. Tap again to stop.';
    $voiceId = $voiceId ?? ('voice-'.uniqid());
@endphp

<div class="tpl-voice-studio"
     data-voice-studio
     data-voice-id="{{ $voiceId }}"
     @if(!empty($voiceSteps)) data-voice-steps='@json($voiceSteps)' @endif>
    <div class="tpl-voice-studio__bar">
        <button type="button" data-voice-guide-mic
                class="tpl-mic-btn tpl-voice-studio__mic"
                aria-label="Start or stop voice input" aria-pressed="false">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/>
            </svg>
        </button>

        <div class="tpl-voice-studio__copy min-w-0 flex-1">
            <div class="flex items-center gap-2 min-w-0">
                <p class="tpl-voice-studio__title truncate">{{ $voiceTitle }}</p>
                <span class="tpl-voice-studio__step truncate" data-voice-step-label>Ready</span>
            </div>
            <p class="tpl-voice-studio__status truncate" data-voice-status>{{ $voiceHint }}</p>
        </div>

        <div class="tpl-voice-studio__actions">
            <select data-voice-lang class="tpl-voice-studio__lang" aria-label="Voice language">
                <option value="en-IN">EN-IN</option>
                <option value="en-US">EN-US</option>
                <option value="hi-IN">HI</option>
            </select>
            <button type="button" data-voice-skip class="tpl-voice-studio__skip">Skip</button>
            {{-- Kept for compatibility; mic starts guided flow when idle --}}
            <button type="button" data-voice-guide class="sr-only">Start guided voice</button>
        </div>
    </div>
</div>

@once
<style>
    .tpl-voice-studio {
        border: 1px solid #e0e7ff;
        border-radius: 1rem;
        background: linear-gradient(135deg, #eef2ff 0%, #ffffff 55%, #f5f3ff 100%);
        box-shadow: 0 8px 24px rgba(79, 70, 229, .06);
        padding: .7rem .85rem;
    }
    .tpl-voice-studio__bar {
        display: flex;
        align-items: center;
        gap: .75rem;
    }
    .tpl-voice-studio__mic {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 1px solid #c7d2fe;
        background: #4f46e5;
        color: #fff;
        box-shadow: 0 8px 18px rgba(79, 70, 229, .28);
        transition: transform .15s ease, background .15s ease, box-shadow .15s ease;
    }
    .tpl-voice-studio__mic:hover {
        transform: translateY(-1px);
        background: #4338ca;
    }
    .tpl-voice-studio__title {
        font-size: .8125rem;
        font-weight: 700;
        color: #312e81;
        line-height: 1.2;
    }
    .tpl-voice-studio__step {
        font-size: .6875rem;
        font-weight: 700;
        color: #4f46e5;
        background: #e0e7ff;
        border-radius: 999px;
        padding: .15rem .5rem;
        max-width: 12rem;
    }
    .tpl-voice-studio__status {
        margin-top: .2rem;
        font-size: .75rem;
        color: #6366f1;
        line-height: 1.3;
    }
    .tpl-voice-studio__actions {
        display: flex;
        align-items: center;
        gap: .4rem;
        flex-shrink: 0;
    }
    .tpl-voice-studio__lang {
        font-size: .6875rem;
        font-weight: 600;
        color: #4338ca;
        background: #fff;
        border: 1px solid #c7d2fe;
        border-radius: .65rem;
        padding: .4rem .5rem;
        outline: none;
    }
    .tpl-voice-studio__lang:focus {
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .15);
    }
    .tpl-voice-studio__skip {
        font-size: .75rem;
        font-weight: 700;
        color: #4f46e5;
        background: #fff;
        border: 1px solid #c7d2fe;
        border-radius: .65rem;
        padding: .4rem .7rem;
        transition: background .15s ease, color .15s ease;
    }
    .tpl-voice-studio__skip:hover {
        background: #eef2ff;
        color: #3730a3;
    }
    .tpl-mic-btn.is-recording {
        background: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #fff !important;
        animation: tplMicPulse 1.1s ease-in-out infinite;
    }
    @keyframes tplMicPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, .35); }
        50% { box-shadow: 0 0 0 10px rgba(220, 38, 38, 0); }
    }
    .tpl-voice-field { position: relative; }
    .tpl-voice-field .tpl-field-mic {
        position: absolute;
        right: .55rem;
        top: 2.15rem;
        z-index: 2;
    }
    .tpl-voice-field textarea ~ .tpl-field-mic { top: 2.35rem; }
    .tpl-voice-field input.tpl-voice-input,
    .tpl-voice-field textarea.tpl-voice-input { padding-right: 2.85rem; }

    .tpl-field-mic {
        width: 2rem;
        height: 2rem;
        border-radius: .65rem;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #6b7280;
        transition: color .15s ease, border-color .15s ease, background .15s ease, box-shadow .15s ease;
    }
    .tpl-field-mic:hover {
        color: #4f46e5;
        border-color: #c7d2fe;
        background: #eef2ff;
        box-shadow: 0 4px 10px rgba(79, 70, 229, .08);
    }

    @media (max-width: 640px) {
        .tpl-voice-studio__bar { align-items: flex-start; }
        .tpl-voice-studio__actions { flex-direction: column; align-items: stretch; }
        .tpl-voice-studio__lang,
        .tpl-voice-studio__skip { width: 100%; text-align: center; }
    }
</style>
@endonce
