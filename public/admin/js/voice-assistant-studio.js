/**
 * Conversational Voice Assistant for creating curriculum starter templates.
 */
(function (window, document) {
    'use strict';

    var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    var NAME_SUGGESTIONS = {
        'Engineering & Technology': ['Engineering & Technology Path', 'Software Foundations Track', 'Full Stack Mentorship Path'],
        'Product Management': ['Product Management Path', 'PM Fundamentals Track', 'Product Sense Roadmap'],
        'Design & UX / UI': ['Design & UX Path', 'UI Craft Track', 'Product Design Journey'],
        'Finance & Investment Banking': ['Finance Mentorship Path', 'Investment Banking Track'],
        'MBA & Management Consulting': ['Consulting Prep Path', 'MBA Mentorship Track'],
        'Marketing & Growth': ['Marketing & Growth Path', 'Growth Marketing Track'],
        'Data Science & AI / ML': ['Data Science & AI Path', 'ML Foundations Track'],
        'Law & Legal': ['Law Mentorship Path', 'Legal Career Track'],
        'Medicine & Healthcare': ['Healthcare Mentorship Path', 'Medicine Career Track'],
        'Arts & Humanities': ['Arts & Humanities Path', 'Creative Careers Track'],
        'Government & Public Policy': ['Public Policy Path', 'Governance Track'],
        'Education & Academia': ['Education Mentorship Path', 'Academia Track'],
        'Entrepreneurship & Startups': ['Startup Founder Path', 'Entrepreneurship Track'],
    };

    var SKILL_SUGGESTIONS = {
        'Engineering & Technology': ['DSA', 'System Design', 'Backend', 'DevOps'],
        'Product Management': ['Product Sense', 'Roadmapping', 'Metrics', 'User Research'],
        'Design & UX / UI': ['Figma', 'UI Systems', 'User Research', 'Prototyping'],
        'Data Science & AI / ML': ['Python', 'ML Basics', 'SQL', 'Statistics'],
        'Marketing & Growth': ['SEO', 'Paid Ads', 'Content', 'Analytics'],
        'default': ['Communication', 'Problem Solving', 'Leadership'],
    };

    function $(sel, root) {
        return (root || document).querySelector(sel);
    }

    function parseJsonAttr(el, name, fallback) {
        try {
            var raw = el.getAttribute(name);
            if (!raw) return fallback;
            return JSON.parse(raw);
        } catch (e) {
            return fallback;
        }
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Speech-to-text often mangles AI/ML and similar tokens.
    var SPEECH_FIXES = [
        [/\bai\s*[\s\/&\-]*m\.?\s*l\b/gi, 'ai ml'],
        [/\ba\.?\s*i\.?\s*\/?\s*m\.?\s*l\.?\b/gi, 'ai ml'],
        [/\baiml\b/gi, 'ai ml'],
        [/\baimal\b/gi, 'ai ml'],
        [/\baml\b/gi, 'ai ml'],
        [/\baiml\b/gi, 'ai ml'],
        [/\bmachine learning\b/gi, 'ml'],
        [/\bartificial intelligence\b/gi, 'ai'],
        [/\bui\s*[\s\/&\-]*ux\b/gi, 'ui ux'],
        [/\bux\s*[\s\/&\-]*ui\b/gi, 'ui ux'],
        [/\buiux\b/gi, 'ui ux'],
        [/\band\b/gi, ' '],
    ];

    var INDUSTRY_ALIASES = {
        'data science ai ml': 'Data Science & AI / ML',
        'data science ai': 'Data Science & AI / ML',
        'data science ml': 'Data Science & AI / ML',
        'data science': 'Data Science & AI / ML',
        'ai ml': 'Data Science & AI / ML',
        'ai': 'Data Science & AI / ML',
        'ml': 'Data Science & AI / ML',
        'machine learning': 'Data Science & AI / ML',
        'artificial intelligence': 'Data Science & AI / ML',
        'datascience': 'Data Science & AI / ML',
        'engineering technology': 'Engineering & Technology',
        'engineering': 'Engineering & Technology',
        'software': 'Engineering & Technology',
        'tech': 'Engineering & Technology',
        'product management': 'Product Management',
        'product': 'Product Management',
        'pm': 'Product Management',
        'design ux ui': 'Design & UX / UI',
        'design ui ux': 'Design & UX / UI',
        'ui ux': 'Design & UX / UI',
        'ux ui': 'Design & UX / UI',
        'design': 'Design & UX / UI',
        'finance investment banking': 'Finance & Investment Banking',
        'finance': 'Finance & Investment Banking',
        'investment banking': 'Finance & Investment Banking',
        'mba management consulting': 'MBA & Management Consulting',
        'mba': 'MBA & Management Consulting',
        'consulting': 'MBA & Management Consulting',
        'marketing growth': 'Marketing & Growth',
        'marketing': 'Marketing & Growth',
        'law legal': 'Law & Legal',
        'law': 'Law & Legal',
        'legal': 'Law & Legal',
        'medicine healthcare': 'Medicine & Healthcare',
        'medicine': 'Medicine & Healthcare',
        'healthcare': 'Medicine & Healthcare',
        'arts humanities': 'Arts & Humanities',
        'government public policy': 'Government & Public Policy',
        'government': 'Government & Public Policy',
        'education academia': 'Education & Academia',
        'education': 'Education & Academia',
        'entrepreneurship startups': 'Entrepreneurship & Startups',
        'entrepreneurship': 'Entrepreneurship & Startups',
        'startup': 'Entrepreneurship & Startups',
        'startups': 'Entrepreneurship & Startups',
    };

    // Short tokens that still matter for industry matching.
    var SHORT_TOKENS = { ai: 1, ml: 1, ui: 1, ux: 1, pm: 1, mba: 1, hr: 1, cse: 1 };

    function normalize(s) {
        return String(s || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').replace(/\s+/g, ' ').trim();
    }

    function autocorrectSpeech(text) {
        var out = String(text || '');
        SPEECH_FIXES.forEach(function (pair) {
            out = out.replace(pair[0], pair[1]);
        });
        return out.replace(/\s+/g, ' ').trim();
    }

    function meaningfulTokens(text) {
        return normalize(text).split(' ').filter(function (p) {
            return p && (p.length > 2 || SHORT_TOKENS[p]);
        });
    }

    function matchIndustry(industries, text) {
        var fixed = autocorrectSpeech(text);
        var n = normalize(fixed);
        if (!n) return null;

        var available = {};
        (industries || []).forEach(function (name) { available[name] = true; });

        // Exact alias hit (speech-friendly).
        if (INDUSTRY_ALIASES[n] && available[INDUSTRY_ALIASES[n]]) {
            return INDUSTRY_ALIASES[n];
        }

        // Partial alias: longest alias key contained in spoken text (or vice versa).
        var aliasBest = null;
        var aliasBestLen = 0;
        Object.keys(INDUSTRY_ALIASES).forEach(function (key) {
            var canon = INDUSTRY_ALIASES[key];
            if (!available[canon]) return;
            if (n.indexOf(key) !== -1 || key.indexOf(n) !== -1) {
                if (key.length > aliasBestLen) {
                    aliasBestLen = key.length;
                    aliasBest = canon;
                }
            }
        });
        if (aliasBest && aliasBestLen >= 2) return aliasBest;

        var best = null;
        var bestScore = 0;
        var spokenParts = meaningfulTokens(fixed);

        industries.forEach(function (name) {
            var nn = normalize(name);
            var score = 0;
            if (nn === n) score = 100;
            else if (nn.indexOf(n) !== -1 || n.indexOf(nn) !== -1) score = 85;
            else {
                var industryParts = meaningfulTokens(name);
                var hits = spokenParts.filter(function (p) {
                    return industryParts.indexOf(p) !== -1 || nn.indexOf(p) !== -1;
                }).length;
                score = hits * 28;
                // Bonus when both data+science or ai+ml appear together.
                if (/\bdata\b/.test(n) && /\bscience\b/.test(n) && /\bdata\b/.test(nn) && /\bscience\b/.test(nn)) {
                    score += 30;
                }
                if (/\bai\b/.test(n) && /\bml\b/.test(n) && (/\bai\b/.test(nn) || /\bml\b/.test(nn) || /\bdata\b/.test(nn))) {
                    score += 35;
                }
            }
            if (score > bestScore) {
                bestScore = score;
                best = name;
            }
        });
        return bestScore >= 40 ? best : null;
    }

    var NAME_WORDS = {
        path: 1, track: 1, roadmap: 1, journey: 1, foundations: 1, foundation: 1,
        mentorship: 1, starter: 1, growth: 1, fundamentals: 1, prep: 1, career: 1,
    };

    function stemToken(p) {
        var t = String(p || '');
        if (t === 'foundations') return 'foundation';
        if (t === 'tracks') return 'track';
        if (t === 'paths') return 'path';
        if (t.length > 4 && t.slice(-1) === 's') return t.slice(0, -1);
        return t;
    }

    function scoreNameSuggestion(spokenNorm, suggestion) {
        var sn = normalize(suggestion);
        if (!spokenNorm || !sn) return 0;
        if (spokenNorm === sn) return 100;
        if (sn.indexOf(spokenNorm) !== -1 || spokenNorm.indexOf(sn) !== -1) return 80;
        var a = meaningfulTokens(spokenNorm).map(stemToken);
        var b = meaningfulTokens(sn).map(stemToken);
        if (!a.length || !b.length) return 0;
        var hits = a.filter(function (p) { return b.indexOf(p) !== -1; }).length;
        return Math.round((hits / Math.max(a.length, b.length)) * 100);
    }

    function matchSuggestedName(raw, industry) {
        var n = normalize(autocorrectSpeech(raw));
        var list = (NAME_SUGGESTIONS[industry] || []).slice();
        // Always consider a few generic clean names too.
        ['Career Mentorship Path', 'Starter Growth Track'].forEach(function (s) {
            if (list.indexOf(s) === -1) list.push(s);
        });
        var best = null;
        var bestScore = 0;
        list.forEach(function (s) {
            var score = scoreNameSuggestion(n, s);
            if (score > bestScore) {
                bestScore = score;
                best = s;
            }
        });
        // "ml foundation strack" → ML Foundations Track (~ high overlap)
        return bestScore >= 55 ? best : null;
    }

    function isMostlyIndustryPhrase(raw, industry) {
        if (!industry) return false;
        var spoken = meaningfulTokens(autocorrectSpeech(raw));
        if (!spoken.length) return false;
        // If they said a name-like word (track/path/foundation…), keep it as a template name.
        if (spoken.some(function (p) { return NAME_WORDS[p]; })) return false;
        var indParts = meaningfulTokens(industry);
        var overlap = spoken.filter(function (p) { return indParts.indexOf(p) !== -1; }).length;
        // Only treat as "repeated industry" when almost every spoken token is industry.
        return overlap >= Math.max(2, Math.ceil(spoken.length * 0.75));
    }

    function cleanTemplateName(raw, industry) {
        var fixed = autocorrectSpeech(raw)
            .replace(/\bstrack\b/gi, 'track')
            .replace(/\btrak\b/gi, 'track')
            .replace(/\bfoundations?\b/gi, function (m) {
                return m.toLowerCase().indexOf('s') !== -1 ? 'Foundations' : 'Foundation';
            });

        // Prefer closest chip/suggestion (e.g. "ml foundation strack" → "ML Foundations Track").
        var suggested = matchSuggestedName(fixed, industry);
        if (suggested) return suggested;

        // Only if they clearly repeated the industry alone, use the default industry path name.
        if (isMostlyIndustryPhrase(fixed, industry)) {
            var defaults = NAME_SUGGESTIONS[industry];
            if (defaults && defaults.length) return defaults[0];
            return industry + ' Path';
        }

        // Light spelling cleanup for free-form names.
        var pretty = fixed
            .replace(/\bai\s*ml\b/gi, 'AI / ML')
            .replace(/\bml\b/gi, 'ML')
            .replace(/\bai\b/gi, 'AI')
            .replace(/\bui\s*ux\b/gi, 'UI / UX')
            .replace(/\s+/g, ' ')
            .trim();
        if (pretty === pretty.toLowerCase() && pretty.length <= 60) {
            pretty = pretty.replace(/\b([a-z])/g, function (m, c) { return c.toUpperCase(); });
        }
        return pretty.slice(0, 100);
    }

    function pickVoice(lang) {
        try {
            var voices = window.speechSynthesis.getVoices() || [];
            return voices.find(function (v) {
                return (v.lang || '').toLowerCase().indexOf((lang || 'en-US').toLowerCase()) === 0
                    && /google|microsoft|samantha|zira|aria|jenny|natural/i.test(v.name || '');
            }) || voices.find(function (v) {
                return (v.lang || '').toLowerCase().indexOf((lang || 'en').toLowerCase().slice(0, 2)) === 0;
            }) || null;
        } catch (e) {
            return null;
        }
    }

    function init(shell) {
        if (!shell || shell.dataset.ready === '1') return;
        shell.dataset.ready = '1';

        var industries = parseJsonAttr(shell, 'data-industries', []);
        var chatEl = $('[data-va-chat]', shell);
        var chipsEl = $('[data-va-chips]', shell);
        var inputEl = $('[data-va-input]', shell);
        var hintEl = $('[data-va-hint]', shell);
        var micBtn = $('[data-va-mic]', shell);
        var sendBtn = $('[data-va-send]', shell);
        var skipBtn = $('[data-va-skip]', shell);
        var explainBtn = $('[data-va-explain]', shell);
        var repeatBtn = $('[data-va-repeat]', shell);
        var closeBtn = $('[data-va-close]', shell);
        var backdrop = $('[data-va-backdrop]', shell);
        var langSelect = $('[data-va-lang]', shell);
        var progressEl = $('[data-va-progress]', shell);
        var progressLabel = $('[data-va-progress-label]', shell);
        var progressCount = $('[data-va-progress-count]', shell);
        var form = $('[data-va-form]', shell);

        var answers = {
            expertise_field: '',
            name: '',
            description: '',
            expertise_tags: '',
            icon: '💻',
            color: '#4f46e5',
            is_active: '1',
        };

        var stepIndex = 0;
        var busy = false;
        var recognition = null;
        var listening = false;

        var steps = [
            {
                key: 'expertise_field',
                label: 'Industry / domain',
                required: true,
                ask: 'Hello! I am Adarsh. Let’s create a starter template together. First — which industry or domain is this for?',
                explain: 'Pick one industry from the mentor list. Mentors in that industry will see this template. For example: Engineering and Technology.',
                hint: 'Say an industry name, or tap a suggestion chip.',
                chips: function () { return industries.slice(0, 8); },
                validate: function (text) {
                    var matched = matchIndustry(industries, text);
                    if (!matched) {
                        return {
                            ok: false,
                            message: 'I could not match that industry. Try saying “Data Science and AI ML”, or tap a suggestion chip.',
                        };
                    }
                    var said = String(text || '').trim();
                    return {
                        ok: true,
                        value: matched,
                        corrected: normalize(autocorrectSpeech(said)) !== normalize(matched),
                    };
                },
            },
            {
                key: 'name',
                label: 'Template name',
                required: true,
                ask: 'Nice choice. What should we name this starter template?',
                explain: 'Keep the name short and clear, like “Engineering & Technology Path”, so mentors recognize it quickly.',
                hint: 'Speak the template name, or choose a suggestion.',
                chips: function () {
                    return NAME_SUGGESTIONS[answers.expertise_field] || ['Career Mentorship Path', 'Starter Growth Track'];
                },
                validate: function (text) {
                    var raw = String(text || '').trim();
                    if (raw.length < 3) return { ok: false, message: 'Please give a name with at least 3 characters.' };
                    var cleaned = cleanTemplateName(raw, answers.expertise_field);
                    if (!cleaned || cleaned.length < 3) {
                        return { ok: false, message: 'Please give a clearer template name.' };
                    }
                    return {
                        ok: true,
                        value: cleaned,
                        corrected: cleaned !== raw,
                    };
                },
            },
            {
                key: 'description',
                label: 'Description',
                required: false,
                ask: 'Would you like to add a short description for mentors? If not, just say skip.',
                explain: 'A one or two line description helps mentors understand what this starter plan covers before they clone it.',
                hint: 'Describe the plan, or press Skip.',
                chips: function () {
                    return [
                        'A structured 6-month mentorship starter plan for this industry.',
                        'Foundations-first path with weekly tasks and mentor checkpoints.',
                    ];
                },
                validate: function (text) {
                    return { ok: true, value: String(text || '').trim() };
                },
            },
            {
                key: 'expertise_tags',
                label: 'Skills',
                required: false,
                ask: 'Last optional detail — any skills to tag for admin reference? Say them with commas, or say skip.',
                explain: 'Skills are only for admin reference. Mentors still see the template based on industry, not these tags.',
                hint: 'Example: DSA, System Design. Or Skip.',
                chips: function () {
                    var list = SKILL_SUGGESTIONS[answers.expertise_field] || SKILL_SUGGESTIONS.default;
                    return [list.join(', ')].concat(list.slice(0, 3));
                },
                validate: function (text) {
                    return { ok: true, value: String(text || '').trim() };
                },
            },
            {
                key: 'confirm',
                label: 'Confirm',
                required: true,
                ask: null,
                explain: 'Review the summary. Say create or yes to save the template.',
                hint: 'Say “create” to save, or “restart” to begin again.',
                chips: function () { return ['Create template', 'Restart']; },
                validate: function (text) {
                    var n = normalize(text);
                    if (/(restart|start over|reset)/.test(n)) return { ok: true, value: '__restart__' };
                    if (/(create|yes|save|confirm|done|ok)/.test(n)) return { ok: true, value: '__create__' };
                    return { ok: false, message: 'Please say “create template” or “restart”.' };
                },
            },
        ];

        function lang() {
            return (langSelect && langSelect.value) || 'en-US';
        }

        function stopSpeech() {
            try { window.speechSynthesis && window.speechSynthesis.cancel(); } catch (e) {}
        }

        function speak(text) {
            if (!window.speechSynthesis || !text) return;
            stopSpeech();
            var u = new SpeechSynthesisUtterance(text);
            u.lang = lang();
            u.rate = 0.94;
            var voice = pickVoice(u.lang);
            if (voice) u.voice = voice;
            window.speechSynthesis.speak(u);
        }

        function scrollChat() {
            if (!chatEl) return;
            chatEl.scrollTop = chatEl.scrollHeight;
        }

        function appendBubble(role, html, meta) {
            var wrap = document.createElement('div');
            wrap.className = 'va-bubble-in flex ' + (role === 'user' ? 'justify-end' : 'justify-start');
            if (role === 'ai') {
                wrap.innerHTML =
                    '<div class="va-msg va-msg--ai max-w-[85%] sm:max-w-[78%]">' +
                        '<div class="va-msg__label mb-1 flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-indigo-500">' +
                            '<span class="va-msg__avatar inline-flex h-5 w-5 items-center justify-center rounded-full bg-indigo-100 text-[9px] text-indigo-700">AD</span> Adarsh' +
                        '</div>' +
                        '<div class="va-msg__bubble rounded-2xl rounded-tl-md bg-white border border-indigo-100 shadow-sm px-4 py-3 text-sm text-slate-700 leading-relaxed">' + html + '</div>' +
                        (meta ? '<div class="va-msg__meta mt-1 text-[10px] text-slate-400">' + escapeHtml(meta) + '</div>' : '') +
                    '</div>';
            } else {
                wrap.innerHTML =
                    '<div class="va-msg va-msg--user max-w-[85%] sm:max-w-[75%]">' +
                        '<div class="va-msg__label mb-1 text-right text-[10px] font-semibold uppercase tracking-wide text-violet-500">You</div>' +
                        '<div class="va-msg__bubble rounded-2xl rounded-tr-md bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-md shadow-indigo-500/20 px-4 py-3 text-sm leading-relaxed">' + html + '</div>' +
                    '</div>';
            }
            chatEl.appendChild(wrap);
            scrollChat();
        }

        function updateProgress() {
            var total = steps.length;
            var done = Math.min(stepIndex, total);
            if (progressEl) progressEl.style.width = ((done / total) * 100) + '%';
            if (progressCount) progressCount.textContent = done + ' / ' + total;
            if (progressLabel) {
                progressLabel.textContent = stepIndex >= total
                    ? 'Ready to create'
                    : (steps[stepIndex].label || 'In progress');
            }
        }

        function renderChips() {
            if (!chipsEl) return;
            chipsEl.innerHTML = '';
            if (stepIndex >= steps.length) return;
            var step = steps[stepIndex];
            var chips = typeof step.chips === 'function' ? step.chips() : (step.chips || []);
            chips.forEach(function (label) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'va-chip inline-flex items-center rounded-full border border-indigo-200 bg-white px-3 py-1.5 text-xs font-medium text-indigo-700 shadow-sm';
                btn.textContent = label;
                btn.addEventListener('click', function () { submitAnswer(label); });
                chipsEl.appendChild(btn);
            });
        }

        function askCurrent(opts) {
            opts = opts || {};
            if (stepIndex >= steps.length) return;
            var step = steps[stepIndex];
            updateProgress();
            renderChips();
            if (hintEl) hintEl.textContent = step.hint || 'Speak or type your answer.';
            if (skipBtn) skipBtn.classList.toggle('invisible', !!step.required);

            var text = step.ask;
            if (step.key === 'confirm') {
                text = 'Here is your summary:<br><br>' +
                    '<strong>Industry:</strong> ' + escapeHtml(answers.expertise_field) + '<br>' +
                    '<strong>Name:</strong> ' + escapeHtml(answers.name) + '<br>' +
                    '<strong>Description:</strong> ' + escapeHtml(answers.description || '—') + '<br>' +
                    '<strong>Skills:</strong> ' + escapeHtml(answers.expertise_tags || '—') +
                    '<br><br>Say <strong>create template</strong> to save, or <strong>restart</strong>.';
                appendBubble('ai', text);
                speak('Here is your summary. Industry: ' + answers.expertise_field + '. Name: ' + answers.name + '. Say create template to save, or restart.');
                return;
            }

            if (!opts.silentAsk) appendBubble('ai', escapeHtml(text));
            if (!opts.silentSpeak) speak(text);
        }

        var wantListening = false;
        var voiceChunks = [];

        function setListening(on) {
            listening = !!on;
            if (micBtn) {
                micBtn.classList.toggle('is-recording', listening);
                micBtn.setAttribute('aria-pressed', listening ? 'true' : 'false');
            }
            if (hintEl) {
                hintEl.textContent = listening
                    ? 'Listening… tap mic again to stop'
                    : 'Tap the mic to start, tap again to stop. You can also type and press send.';
            }
        }

        function joinedVoice() {
            return voiceChunks.join(' ').replace(/\s+/g, ' ').trim();
        }

        function stopMic(commit) {
            wantListening = false;
            setListening(false);
            try { recognition && recognition.stop(); } catch (e) {}
            recognition = null;

            var full = joinedVoice();
            voiceChunks = [];
            if (commit && full) {
                if (inputEl) inputEl.value = full;
                submitAnswer(full);
            }
        }

        function startMic() {
            if (!SpeechRecognition) {
                appendBubble('ai', 'Voice input needs Chrome or Edge on localhost or HTTPS. You can still type your answers.');
                return;
            }
            stopSpeech();
            wantListening = false;
            try { recognition && recognition.abort(); } catch (e) {}

            voiceChunks = [];
            recognition = new SpeechRecognition();
            recognition.continuous = true;
            recognition.interimResults = true;
            recognition.lang = lang();
            recognition.maxAlternatives = 1;
            wantListening = true;

            recognition.onstart = function () { setListening(true); };
            recognition.onerror = function (event) {
                if (wantListening && (event.error === 'no-speech' || event.error === 'aborted')) return;
                wantListening = false;
                setListening(false);
                if (event.error === 'not-allowed') {
                    appendBubble('ai', 'Microphone permission is blocked. Allow the mic, or type your answer.');
                }
            };
            recognition.onend = function () {
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
                setListening(false);
            };
            recognition.onresult = function (event) {
                var interim = '';
                for (var i = event.resultIndex; i < event.results.length; i++) {
                    var piece = event.results[i][0].transcript;
                    if (event.results[i].isFinal) {
                        var bit = String(piece || '').trim();
                        if (bit) voiceChunks.push(bit);
                    } else {
                        interim += piece;
                    }
                }
                var finals = joinedVoice();
                var live = (finals + (interim ? (finals ? ' ' : '') + interim : '')).trim();
                if (inputEl) inputEl.value = live;
                // Keep recording until user taps mic again.
            };

            try {
                recognition.start();
            } catch (e) {
                wantListening = false;
                setListening(false);
            }
        }

        function isSkipIntent(text) {
            var n = normalize(text);
            if (!n) return false;
            return /^(skip|skip this|skip it|skip please|pass|no thanks|no thank you|none|nothing|nope|next|leave it|leave blank|not now)$/.test(n)
                || /^(i )?want to skip$/.test(n)
                || /^skip (this|the) (one|field|step|question)$/.test(n);
        }

        function submitAnswer(raw) {
            if (busy) return;
            var text = String(raw || '').trim();
            if (!text) return;
            if (stepIndex >= steps.length) return;

            var step = steps[stepIndex];

            // Spoken/typed "skip" must skip optional fields — never save "skip" as the answer.
            if (step.key !== 'confirm' && isSkipIntent(text)) {
                if (inputEl) inputEl.value = '';
                skipStep();
                return;
            }

            appendBubble('user', escapeHtml(text));
            if (inputEl) inputEl.value = '';

            var result = step.validate(text);
            if (!result.ok) {
                appendBubble('ai', escapeHtml(result.message || 'Please try again.'));
                speak(result.message || 'Please try again.');
                renderChips();
                return;
            }

            if (step.key === 'confirm') {
                if (result.value === '__restart__') {
                    restart();
                    return;
                }
                createTemplate();
                return;
            }

            // Extra guard: never persist skip-like values on optional text fields.
            var value = result.value;
            if (!step.required && isSkipIntent(value)) value = '';

            answers[step.key] = value;
            if (step.key === 'expertise_field' || result.corrected) {
                var confirmMsg = result.corrected
                    ? ('Got it — I autocorrected that to “' + result.value + '”.')
                    : ('Got it — ' + result.value + '.');
                appendBubble('ai', 'Got it — <strong>' + escapeHtml(result.value) + '</strong>.');
                speak(confirmMsg);
            }

            stepIndex += 1;
            updateProgress();
            setTimeout(function () { askCurrent(); }, (step.key === 'expertise_field' || result.corrected) ? 520 : 280);
        }

        function skipStep() {
            if (stepIndex >= steps.length) return;
            var step = steps[stepIndex];
            if (step.required) {
                appendBubble('ai', 'This field is required. Please answer it to continue.');
                speak('This field is required. Please answer it to continue.');
                return;
            }
            appendBubble('user', 'Skip');
            answers[step.key] = '';
            stepIndex += 1;
            updateProgress();
            setTimeout(function () { askCurrent(); }, 220);
        }

        function createTemplate() {
            if (!form) return;
            busy = true;
            appendBubble('ai', 'Creating your starter template now…');
            speak('Creating your starter template now.');

            form.querySelector('[name=expertise_field]').value = answers.expertise_field;
            form.querySelector('[name=name]').value = answers.name;
            form.querySelector('[name=description]').value = answers.description || '';
            form.querySelector('[name=expertise_tags]').value = answers.expertise_tags || '';
            form.querySelector('[name=icon]').value = answers.icon || '💻';
            form.querySelector('[name=color]').value = answers.color || '#4f46e5';
            form.querySelector('[name=is_active]').value = answers.is_active || '1';
            form.submit();
        }

        function restart() {
            busy = false;
            stopMic(false);
            stopSpeech();
            answers.expertise_field = '';
            answers.name = '';
            answers.description = '';
            answers.expertise_tags = '';
            stepIndex = 0;
            if (chatEl) chatEl.innerHTML = '';
            askCurrent();
        }

        function openStudio() {
            shell.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (!chatEl.childElementCount) {
                askCurrent();
            } else {
                var step = steps[Math.min(stepIndex, steps.length - 1)];
                speak(step.key === 'confirm'
                    ? 'Let us continue. Review the summary, then say create template.'
                    : step.ask);
            }
            setTimeout(function () { inputEl && inputEl.focus(); }, 200);
        }

        function closeStudio() {
            stopMic(false);
            stopSpeech();
            shell.classList.add('hidden');
            if (!document.querySelector('.tpl-modal.flex') && !document.querySelector('[data-voice-guide-modal].flex')) {
                document.body.style.overflow = '';
            }
        }

        micBtn && micBtn.addEventListener('click', function () {
            if (listening || wantListening) stopMic(true);
            else startMic();
        });

        sendBtn && sendBtn.addEventListener('click', function () {
            submitAnswer(inputEl && inputEl.value);
        });

        inputEl && inputEl.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitAnswer(inputEl.value);
            }
        });

        skipBtn && skipBtn.addEventListener('click', skipStep);

        explainBtn && explainBtn.addEventListener('click', function () {
            if (stepIndex >= steps.length) return;
            var step = steps[stepIndex];
            appendBubble('ai', escapeHtml(step.explain || step.ask));
            speak(step.explain || step.ask);
        });

        repeatBtn && repeatBtn.addEventListener('click', function () {
            if (stepIndex >= steps.length) return;
            var step = steps[stepIndex];
            if (step.key === 'confirm') {
                askCurrent({ silentAsk: false });
                return;
            }
            appendBubble('ai', escapeHtml(step.ask), 'Repeated');
            speak(step.ask);
        });

        closeBtn && closeBtn.addEventListener('click', closeStudio);
        backdrop && backdrop.addEventListener('click', closeStudio);

        shell.querySelectorAll('[data-open-voice-guide]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                stopMic(false);
                stopSpeech();
            });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !shell.classList.contains('hidden')) {
                // Prefer closing nested video guide first if open
                var guide = document.querySelector('[data-voice-guide-modal].flex');
                if (guide) return;
                closeStudio();
            }
        });

        shell._openVoiceStudio = openStudio;
        shell._closeVoiceStudio = closeStudio;

        if (window.speechSynthesis) {
            window.speechSynthesis.getVoices();
            window.speechSynthesis.onvoiceschanged = function () {
                window.speechSynthesis.getVoices();
            };
        }
    }

    function boot() {
        var shell = document.querySelector('[data-voice-studio-shell]');
        if (!shell) return;
        init(shell);

        document.querySelectorAll('[data-open-voice-studio]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                shell._openVoiceStudio && shell._openVoiceStudio();
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})(window, document);
