/**
 * Browser speech → text helper (Srijan-style mic flow).
 * Uses Web Speech API; no audio is uploaded to the server.
 */
(function (window, document) {
    'use strict';

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    function setListeningUi(btn, on, statusEl, message) {
        if (btn) {
            btn.classList.toggle('is-recording', !!on);
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        }
        if (statusEl && message != null) statusEl.textContent = message;
    }

    function createSession(options) {
        let recognition = null;
        let listening = false;
        let wantListening = false;
        let chunks = [];
        let baseValue = '';
        const opts = options || {};

        function joinedTranscript() {
            return chunks.join(' ').replace(/\s+/g, ' ').trim();
        }

        function writeTarget(text, isFinalWrite) {
            const target = typeof opts.getTarget === 'function' ? opts.getTarget() : opts.target;
            if (!target || (target.tagName !== 'INPUT' && target.tagName !== 'TEXTAREA')) return;

            const append = typeof opts.getAppend === 'function' ? opts.getAppend() : opts.append;
            const newline = typeof opts.getNewline === 'function' ? opts.getNewline() : opts.newline;
            let next = text;

            if (newline && baseValue.trim()) {
                next = baseValue.replace(/\s+$/, '') + (text ? '\n' + text : '');
            } else if (append && baseValue.trim()) {
                next = (baseValue.replace(/\s+$/, '') + (text ? ' ' + text : '')).trim();
            }

            target.value = next;
            target.dispatchEvent(new Event('input', { bubbles: true }));
            if (isFinalWrite) target.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function stop(fromUser) {
            wantListening = false;
            listening = false;
            try { recognition && recognition.stop(); } catch (e) {}
            recognition = null;

            const full = joinedTranscript();
            if (full) writeTarget(full, true);

            setListeningUi(
                opts.button,
                false,
                opts.status,
                fromUser && full
                    ? ('Captured: ' + full)
                    : (opts.idleText || 'Tap mic to start, tap again to stop')
            );

            if (fromUser) opts.onStop && opts.onStop(full);
            chunks = [];
        }

        function start() {
            if (!SpeechRecognition) {
                const msg = 'Voice input needs Chrome or Edge on HTTPS/localhost.';
                setListeningUi(opts.button, false, opts.status, msg);
                opts.onError && opts.onError(msg);
                return;
            }

            try { window.speechSynthesis && window.speechSynthesis.cancel(); } catch (e) {}
            wantListening = false;
            try { recognition && recognition.abort(); } catch (e) {}

            chunks = [];
            const target = typeof opts.getTarget === 'function' ? opts.getTarget() : opts.target;
            baseValue = target && (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA')
                ? String(target.value || '')
                : '';

            recognition = new SpeechRecognition();
            // Keep listening until the user taps the mic again.
            recognition.continuous = true;
            recognition.interimResults = true;
            recognition.lang = (typeof opts.getLang === 'function' ? opts.getLang() : opts.lang) || 'en-IN';
            recognition.maxAlternatives = 1;

            wantListening = true;

            recognition.onstart = function () {
                listening = true;
                setListeningUi(
                    opts.button,
                    true,
                    opts.status,
                    opts.listeningText || 'Listening… tap mic to stop'
                );
            };

            recognition.onerror = function (event) {
                // Ignore benign pause errors while user still wants to record.
                if (wantListening && (event.error === 'no-speech' || event.error === 'aborted')) {
                    return;
                }
                wantListening = false;
                listening = false;
                const msg = event.error === 'not-allowed'
                    ? 'Microphone permission blocked. Allow mic and try again.'
                    : (event.error === 'aborted'
                        ? (opts.idleText || 'Tap mic to start, tap again to stop')
                        : ('Voice error: ' + (event.error || 'unknown')));
                setListeningUi(opts.button, false, opts.status, msg);
                if (event.error !== 'aborted') opts.onError && opts.onError(msg);
            };

            recognition.onend = function () {
                // Chrome often ends a session after a pause — restart until user stops.
                if (wantListening) {
                    try {
                        recognition.start();
                    } catch (e) {
                        setTimeout(function () {
                            if (!wantListening || !recognition) return;
                            try { recognition.start(); } catch (err) {}
                        }, 180);
                    }
                    return;
                }
                listening = false;
                setListeningUi(opts.button, false, opts.status, opts.idleText || 'Tap mic to start, tap again to stop');
            };

            recognition.onresult = function (event) {
                let interim = '';
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    const piece = event.results[i][0].transcript;
                    if (event.results[i].isFinal) {
                        const bit = String(piece || '').trim();
                        if (bit) chunks.push(bit);
                    } else {
                        interim += piece;
                    }
                }

                const finals = joinedTranscript();
                const live = (finals + (interim ? (finals ? ' ' : '') + interim : '')).trim();
                if (live && opts.status) opts.status.textContent = live;

                if (finals) {
                    writeTarget(finals, false);
                    opts.onResult && opts.onResult(finals, true);
                } else if (interim.trim()) {
                    opts.onResult && opts.onResult(interim.trim(), false);
                }
                // Do not auto-stop — user must tap mic again.
            };

            try {
                recognition.start();
            } catch (e) {
                wantListening = false;
                listening = false;
                setListeningUi(opts.button, false, opts.status, opts.idleText || 'Tap mic to start, tap again to stop');
                opts.onError && opts.onError(e.message || 'Could not start microphone');
            }
        }

        function toggle() {
            if (listening || wantListening) stop(true);
            else start();
        }

        return {
            start,
            stop: function () { stop(false); },
            stopAndCommit: function () { stop(true); },
            toggle,
            isListening: function () { return listening || wantListening; },
            updateOptions: function (next) { Object.assign(opts, next || {}); },
        };
    }

    function bindMicButton(options) {
        const btn = options.button;
        if (!btn) return null;
        if (btn._vedrixVoiceSession) return btn._vedrixVoiceSession;

        const session = createSession(options);
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            session.toggle();
        });
        btn._vedrixVoiceSession = session;
        return session;
    }

    function matchOption(select, spoken) {
        if (!select || !spoken) return null;
        const needle = spoken.toLowerCase().replace(/[^\w\s&/+-]/g, ' ').replace(/\s+/g, ' ').trim();
        const options = Array.from(select.options).filter((o) => o.value);

        let best = null;
        let bestScore = 0;
        options.forEach((opt) => {
            const label = String(opt.textContent || '').toLowerCase();
            const value = String(opt.value || '').toLowerCase();
            let score = 0;
            if (label === needle || value === needle) score = 100;
            else if (label.includes(needle) || needle.includes(label)) score = 80;
            else {
                const words = needle.split(' ').filter(Boolean);
                const hits = words.filter((w) => label.includes(w) || value.includes(w)).length;
                score = hits ? (hits / words.length) * 60 : 0;
            }
            if (score > bestScore) {
                bestScore = score;
                best = opt;
            }
        });

        return bestScore >= 40 ? best : null;
    }

    function parseSteps(root, explicitSteps) {
        if (Array.isArray(explicitSteps) && explicitSteps.length) return explicitSteps;
        const raw = root.getAttribute('data-voice-steps');
        if (!raw) return [];
        try {
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    /**
     * Wire a Voice fill studio panel + field mics inside its form/scope.
     */
    function initStudio(root, options) {
        if (!root || root.dataset.voiceReady === '1') return null;
        root.dataset.voiceReady = '1';

        const opts = options || {};
        const scope = opts.scope || root.closest('form') || root.parentElement || document;
        const steps = parseSteps(root, opts.steps);
        const statusEl = root.querySelector('[data-voice-status]');
        const stepLabel = root.querySelector('[data-voice-step-label]');
        const langSelect = root.querySelector('[data-voice-lang]');
        const guideBtn = root.querySelector('[data-voice-guide]');
        const skipBtn = root.querySelector('[data-voice-skip]');
        const guideMic = root.querySelector('[data-voice-guide-mic]');

        let stepIndex = -1;
        let listenTimer = null;

        const guideSession = createSession({
            button: guideMic,
            status: statusEl,
            lang: 'en-IN',
            idleText: 'Tap mic to start, tap again to stop',
            listeningText: 'Listening… tap mic to stop',
        });

        function lang() {
            return langSelect?.value || 'en-IN';
        }

        function field(key) {
            return scope.querySelector('[data-voice-field="' + key + '"]');
        }

        function clearListenTimer() {
            if (listenTimer) {
                clearTimeout(listenTimer);
                listenTimer = null;
            }
        }

        function speakableLabel(step) {
            return String(step.label || step.key || 'field')
                .replace(/\s*\/\s*/g, ' or ')
                .replace(/\s+/g, ' ')
                .trim();
        }

        function announcementFor(step) {
            const label = speakableLabel(step);
            const prompt = (step.prompt || ('Please say the ' + label + '.')).trim();
            // Always lead with the full field name so TTS is clear and complete.
            return 'Next field: ' + label + '. ' + prompt;
        }

        function setStepUi(index) {
            stepIndex = index;
            if (index < 0 || index >= steps.length) {
                if (stepLabel) stepLabel.textContent = 'Ready';
                if (statusEl) {
                    statusEl.textContent = SpeechRecognition
                        ? 'Tap mic to start, tap again to stop'
                        : 'Voice needs Chrome or Edge on localhost/HTTPS.';
                }
                guideSession.updateOptions({ idleText: statusEl?.textContent });
                return;
            }
            const step = steps[index];
            if (stepLabel) stepLabel.textContent = 'Step ' + (index + 1) + '/' + steps.length + ' · ' + (step.label || step.key);
            if (statusEl) statusEl.textContent = announcementFor(step);
            field(step.key)?.focus();
        }

        function applyTranscript(step, text) {
            const el = field(step.key);
            if (!el) return false;
            const lower = text.toLowerCase().trim();
            if (lower === 'skip' || lower === 'skip this') return true;

            if (el.tagName === 'SELECT' || step.type === 'select') {
                const opt = matchOption(el, text);
                if (!opt) {
                    if (statusEl) statusEl.textContent = 'Could not match. Try again, or tap Skip.';
                    return false;
                }
                el.value = opt.value;
                el.dispatchEvent(new Event('change', { bubbles: true }));
                return true;
            }

            if (step.append && el.value.trim()) {
                el.value = (el.value.replace(/\s+$/, '') + ' ' + text).trim();
            } else if (step.newline && el.value.trim()) {
                el.value = el.value.replace(/\s+$/, '') + '\n' + text;
            } else {
                el.value = text;
            }
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
            return true;
        }

        function finishGuidedAnswer(text) {
            if (stepIndex < 0 || stepIndex >= steps.length) return;
            const step = steps[stepIndex];
            const answer = String(text || '').trim();
            if (!answer) {
                if (statusEl) statusEl.textContent = 'Nothing captured. Tap mic, speak, then tap mic again to stop.';
                return;
            }
            if (!applyTranscript(step, answer)) {
                if (statusEl) statusEl.textContent = 'Could not match. Tap mic and try again, or tap Skip.';
                return;
            }
            const next = stepIndex + 1;
            if (next >= steps.length) {
                setStepUi(-1);
                if (statusEl) statusEl.textContent = 'All guided fields filled. Review and save.';
                speakOut('All set. Please review and save.', lang());
                return;
            }
            goToStep(next);
        }

        function listenForCurrentStep(delayMs) {
            if (stepIndex < 0 || stepIndex >= steps.length) return;
            const step = steps[stepIndex];
            const capturedIndex = stepIndex;
            clearListenTimer();

            guideSession.updateOptions({
                getLang: lang,
                listeningText: 'Listening for ' + speakableLabel(step) + '… tap mic to stop',
                idleText: announcementFor(step),
                getTarget: function () { return null; },
                onResult: function (text, isFinal) {
                    if (!isFinal || !statusEl) return;
                    statusEl.textContent = text;
                },
                // Advance only when user taps mic to stop — not when they pause speaking.
                onStop: function (full) {
                    if (stepIndex !== capturedIndex) return;
                    finishGuidedAnswer(full);
                },
            });

            listenTimer = setTimeout(function () {
                listenTimer = null;
                if (stepIndex !== capturedIndex) return;
                guideSession.start();
            }, delayMs == null ? 350 : delayMs);
        }

        function goToStep(index, options) {
            const cfg = options || {};
            clearListenTimer();
            guideSession.stop();
            setStepUi(index);
            if (index < 0 || index >= steps.length) return;

            if (cfg.speak === false) {
                listenForCurrentStep(cfg.listenDelay == null ? 350 : cfg.listenDelay);
                return;
            }

            const step = steps[index];
            if (statusEl) statusEl.textContent = 'Speaking… ' + speakableLabel(step);
            speakOut(announcementFor(step), lang()).then(function () {
                if (stepIndex !== index) return;
                if (statusEl) statusEl.textContent = 'Listening… ' + speakableLabel(step);
                listenForCurrentStep(400);
            });
        }

        function beginGuided() {
            if (!SpeechRecognition) {
                if (statusEl) statusEl.textContent = 'Voice needs Chrome or Edge on localhost/HTTPS.';
                return;
            }
            if (!steps.length) {
                if (statusEl) statusEl.textContent = 'No guided steps configured. Use field mics instead.';
                return;
            }
            stopSpeaking();
            guideSession.stop();
            goToStep(0);
        }

        guideBtn?.addEventListener('click', beginGuided);

        guideMic?.addEventListener('click', function (e) {
            e.preventDefault();
            if (guideSession.isListening()) {
                // Commit current answer and move on.
                guideSession.stopAndCommit();
                return;
            }
            if (stepIndex < 0) {
                beginGuided();
                return;
            }
            listenForCurrentStep(0);
        });

        skipBtn?.addEventListener('click', function () {
            clearListenTimer();
            guideSession.stop();
            stopSpeaking();
            if (stepIndex < 0) return;
            const next = stepIndex + 1;
            if (next >= steps.length) {
                setStepUi(-1);
                return;
            }
            goToStep(next);
        });

        scope.querySelectorAll('[data-dictate-for]').forEach(function (btn) {
            if (btn._vedrixVoiceSession) return;
            const key = btn.getAttribute('data-dictate-for');
            bindMicButton({
                button: btn,
                getTarget: function () { return field(key); },
                status: statusEl,
                getLang: lang,
                getAppend: function () {
                    const step = steps.find(function (s) { return s.key === key; });
                    return !!(step && step.append);
                },
                getNewline: function () {
                    const step = steps.find(function (s) { return s.key === key; });
                    return !!(step && step.newline);
                },
                idleText: 'Tap mic to start, tap again to stop',
                listeningText: 'Listening… tap mic to stop',
            });
        });

        const modal = root.closest('.tpl-modal, [id$="-modal"], .fixed.inset-0');
        if (modal && !modal.dataset.voiceCloseBound) {
            modal.dataset.voiceCloseBound = '1';
            const observer = new MutationObserver(function () {
                if (modal.classList.contains('hidden')) {
                    clearListenTimer();
                    guideSession.stop();
                    stopSpeaking();
                    setStepUi(-1);
                }
            });
            observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
        }

        if (!SpeechRecognition && statusEl) {
            statusEl.textContent = 'Voice needs Chrome or Edge on localhost/HTTPS.';
        }

        return { beginGuided, stop: function () { clearListenTimer(); guideSession.stop(); stopSpeaking(); } };
    }

    function speakOut(text, lang) {
        return new Promise(function (resolve) {
            if (!window.speechSynthesis || !text) {
                resolve();
                return;
            }

            try { window.speechSynthesis.cancel(); } catch (e) {}

            const u = new SpeechSynthesisUtterance(text);
            u.lang = lang || 'en-IN';
            u.rate = 0.95;

            let settled = false;
            const finish = function () {
                if (settled) return;
                settled = true;
                resolve();
            };

            u.onend = finish;
            u.onerror = finish;
            // Some browsers skip onend; never block the mic forever.
            const fallbackMs = Math.min(15000, Math.max(2500, String(text).length * 75));
            setTimeout(finish, fallbackMs);

            try {
                window.speechSynthesis.speak(u);
            } catch (e) {
                finish();
            }
        });
    }

    function stopSpeaking() {
        try { window.speechSynthesis && window.speechSynthesis.cancel(); } catch (e) {}
    }

    function bootAll(container) {
        (container || document).querySelectorAll('[data-voice-studio]').forEach(function (root) {
            initStudio(root);
        });
    }

    window.VedrixVoice = {
        isSupported: !!SpeechRecognition,
        createSession,
        bindMicButton,
        matchOption,
        initStudio,
        bootAll,
        stopSpeaking,
        speak: speakOut,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { bootAll(); });
    } else {
        bootAll();
    }
})(window, document);
