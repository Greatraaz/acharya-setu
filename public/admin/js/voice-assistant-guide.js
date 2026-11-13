/**
 * Adarsh curriculum walkthrough — full forms (template, month, week, task, MCQ)
 * with real field UI previews and YouTube-style playback controls.
 */
(function (window, document) {
    'use strict';

    var MIC_SVG = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/></svg>';

    var FORMS = {
        template: {
            title: 'New starter template',
            fields: [
                { key: 'name', label: 'Template name', required: true, type: 'text', placeholder: 'e.g. Engineering & Technology Path', sample: 'Engineering & Technology Path', mic: true },
                { key: 'expertise_field', label: 'Industry / domain', required: true, type: 'select', options: ['— Select industry —', 'Engineering & Technology', 'Product Management', 'Data Science & AI / ML'], sample: 'Engineering & Technology', mic: false },
                { key: 'icon', label: 'Icon (emoji)', type: 'text', placeholder: '💻', sample: '💻', mic: false, center: true },
                { key: 'color', label: 'Accent color', type: 'color', sample: '#4f46e5', mic: false },
                { key: 'description', label: 'Description', type: 'textarea', placeholder: 'Shown to mentors when they pick a starter plan', sample: 'A structured 6-month mentorship starter plan for this industry.', mic: true, rows: 3 },
                { key: 'expertise_tags', label: 'Skills (optional)', type: 'text', placeholder: 'System Design, Product Sense…', sample: 'DSA, System Design, Backend', mic: true, hint: 'Comma-separated. For admin reference — visibility is by industry.' },
                { key: 'is_active', label: 'Active', type: 'toggle', sample: true, hint: 'Visible to mentors in this industry' },
            ],
        },
        month: {
            title: 'Add month',
            fields: [
                { key: 'month_number', label: 'Month Number', required: true, type: 'select', options: ['Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5', 'Month 6'], sample: 'Month 1', mic: false },
                { key: 'theme', label: 'Theme', type: 'text', placeholder: 'Foundation, Deep Dive…', sample: 'Foundation', mic: true },
                { key: 'title', label: 'Title', required: true, type: 'text', placeholder: 'e.g. Foundations of Product Thinking', sample: 'Foundations of Product Thinking', mic: true },
                { key: 'description', label: 'Description', type: 'textarea', placeholder: 'What will the mentee accomplish this month?', sample: 'Build core fundamentals and complete the first milestone.', mic: true, rows: 2 },
                { key: 'learning_outcomes', label: 'Learning Outcomes', type: 'textarea', placeholder: 'One outcome per line', sample: 'Understand REST APIs\nBuild a CRUD app\nDeploy to production', mic: true, rows: 3, hint: 'One per line — shown as bullet points to mentees.', mono: true },
                { key: 'milestone_badge', label: 'Milestone Badge Name', type: 'text', placeholder: 'e.g. Foundation Builder', sample: 'Foundation Builder', mic: true, hint: 'Earned on 100% completion of this month.' },
            ],
        },
        week: {
            title: 'Add week',
            fields: [
                { key: 'week_number', label: 'Week Number', required: true, type: 'select', options: ['Week 1', 'Week 2', 'Week 3', 'Week 4'], sample: 'Week 1', mic: false },
                { key: 'title', label: 'Title', required: true, type: 'text', placeholder: 'Week title…', sample: 'Orientation & Goal Setting', mic: true },
                { key: 'focus', label: 'Focus Statement', type: 'text', placeholder: 'Core focus of this week…', sample: 'Clarify goals and set the weekly rhythm', mic: true },
                { key: 'mentor_guide', label: 'Mentor Guide (private)', type: 'textarea', placeholder: 'Guidance for the mentor on running this week…', sample: 'Start with rapport. Align on goals. Assign first task.', mic: true, rows: 3 },
                { key: 'video_url', label: 'Intro Video URL', type: 'text', placeholder: 'https://youtube.com/…', sample: 'https://youtube.com/watch?v=example', mic: false },
            ],
        },
        task: {
            title: 'Add task',
            fields: [
                { key: 'title', label: 'Title', required: true, type: 'text', placeholder: 'Task title', sample: 'Write your learning goals', mic: true },
                { key: 'description', label: 'Description', type: 'textarea', placeholder: 'Describe the task…', sample: 'List 3 goals for this month and share with your mentor.', mic: true, rows: 3 },
                { key: 'type', label: 'Type', type: 'select', options: ['📝 Task', '📖 Reading', '🎬 Video', '🧪 Practice'], sample: '📝 Task', mic: false },
                { key: 'submission_type', label: 'Submission Type', type: 'select', options: ['None', 'Text', 'File', 'Link'], sample: 'Text', mic: false },
                { key: 'estimated_minutes', label: 'Est. Minutes', type: 'text', placeholder: '30', sample: '30', mic: false },
                { key: 'is_required', label: 'Required', type: 'toggle', sample: true },
            ],
        },
        mcq: {
            title: 'Add MCQ',
            fields: [
                { key: 'topic', label: 'Topic', type: 'text', placeholder: 'Topic name', sample: 'REST Basics', mic: true },
                { key: 'question', label: 'Question', required: true, type: 'textarea', placeholder: 'Full question…', sample: 'Which HTTP method is typically used to create a resource?', mic: true, rows: 2 },
                { key: 'option_0', label: 'Option A', type: 'text', placeholder: 'Option A', sample: 'GET', mic: true },
                { key: 'option_1', label: 'Option B', type: 'text', placeholder: 'Option B', sample: 'POST', mic: true },
                { key: 'option_2', label: 'Option C', type: 'text', placeholder: 'Option C', sample: 'DELETE', mic: true },
                { key: 'option_3', label: 'Option D', type: 'text', placeholder: 'Option D', sample: 'PATCH', mic: true },
                { key: 'explanation', label: 'Explanation', type: 'textarea', placeholder: 'Why this answer is correct…', sample: 'POST is commonly used to create a new resource.', mic: true, rows: 2 },
                { key: 'difficulty', label: 'Difficulty', type: 'select', options: ['Easy', 'Medium', 'Hard'], sample: 'Easy', mic: false },
            ],
        },
    };

    var STEPS = [
        {
            title: 'Hi, I am Adarsh',
            kicker: 'Adarsh · Curriculum guide',
            body: 'I will walk you through the full curriculum builder — template, months, weeks, tasks, and MCQs — using the same forms you fill manually.',
            caption: 'Hi there. I am Adarsh. Today I will show every curriculum form, field by field, just like a teammate sitting with you.',
            speak: 'Hi there. I am Adarsh. Today I will show every curriculum form, field by field, just like a teammate sitting with you.',
            form: 'template',
            highlight: null,
            filled: {},
            fieldNote: 'We start with the starter template form',
            durationSec: 8,
        },
        {
            title: 'Template: Industry',
            kicker: 'Template form · Required',
            body: 'Industry or domain decides which mentors can see this starter plan. Pick the same industry mentors choose during onboarding.',
            caption: 'On the template form, first choose Industry or domain. Mentors only see the template that matches their industry.',
            speak: 'On the template form, first choose Industry or domain. Mentors only see the template that matches their industry.',
            form: 'template',
            highlight: 'expertise_field',
            filled: { expertise_field: true },
            fieldNote: 'Highlighting Industry / domain',
            durationSec: 9,
        },
        {
            title: 'Template: Name',
            kicker: 'Template form · Required',
            body: 'Template name should be short and clear. Mentors will recognize it when they clone the plan for a mentee.',
            caption: 'Next is Template name. Keep it friendly and clear, like Engineering and Technology Path. You can speak it or type it.',
            speak: 'Next is Template name. Keep it friendly and clear, like Engineering and Technology Path. You can speak it or type it.',
            form: 'template',
            highlight: 'name',
            filled: { expertise_field: true, name: true },
            fieldNote: 'Highlighting Template name',
            durationSec: 8,
        },
        {
            title: 'Template: Description & Skills',
            kicker: 'Template form · Optional',
            body: 'Description helps mentors understand the plan. Skills are optional admin tags. Then save and open Build to add months.',
            caption: 'Description and Skills are optional. Fill them by voice, or say skip. Then create the template and click Build.',
            speak: 'Description and Skills are optional. Fill them by voice, or say skip. Then create the template and click Build.',
            form: 'template',
            highlight: 'description',
            filled: { expertise_field: true, name: true, description: true, expertise_tags: true, icon: true, color: true, is_active: true },
            fieldNote: 'Highlighting Description',
            durationSec: 9,
        },
        {
            title: 'Month form',
            kicker: 'Build · Add month',
            body: 'Inside Build, add months one by one. Month number, title, theme, description, learning outcomes, and milestone badge all support voice fill.',
            caption: 'Now the Month form. Choose the month number, then speak the title, theme, description, learning outcomes, and badge name.',
            speak: 'Now the Month form. Choose the month number, then speak the title, theme, description, learning outcomes, and badge name.',
            form: 'month',
            highlight: 'title',
            filled: { month_number: true, theme: true, title: true },
            fieldNote: 'Month form preview',
            durationSec: 10,
        },
        {
            title: 'Month: Outcomes & Badge',
            kicker: 'Build · Month details',
            body: 'Learning outcomes are one per line for mentees. The milestone badge is earned when the month is fully completed.',
            caption: 'For learning outcomes, say one line at a time. The milestone badge is the reward name mentees earn at one hundred percent.',
            speak: 'For learning outcomes, say one line at a time. The milestone badge is the reward name mentees earn at one hundred percent.',
            form: 'month',
            highlight: 'learning_outcomes',
            filled: { month_number: true, theme: true, title: true, description: true, learning_outcomes: true, milestone_badge: true },
            fieldNote: 'Highlighting Learning Outcomes',
            durationSec: 9,
        },
        {
            title: 'Week form',
            kicker: 'Build · Add week',
            body: 'Each month has weeks. Fill week number, title, focus statement, private mentor guide, and optional intro video URL.',
            caption: 'Next is the Week form. Speak the week title and focus. Mentor guide is private coaching notes for the mentor only.',
            speak: 'Next is the Week form. Speak the week title and focus. Mentor guide is private coaching notes for the mentor only.',
            form: 'week',
            highlight: 'title',
            filled: { week_number: true, title: true, focus: true },
            fieldNote: 'Week form preview',
            durationSec: 9,
        },
        {
            title: 'Week: Mentor guide',
            kicker: 'Build · Week details',
            body: 'Use Guided voice or the field mic beside Mentor Guide. Then save the week and open it to add tasks.',
            caption: 'You can dictate the mentor guide, add an intro video link if needed, then save and open the week to add tasks.',
            speak: 'You can dictate the mentor guide, add an intro video link if needed, then save and open the week to add tasks.',
            form: 'week',
            highlight: 'mentor_guide',
            filled: { week_number: true, title: true, focus: true, mentor_guide: true, video_url: true },
            fieldNote: 'Highlighting Mentor Guide',
            durationSec: 8,
        },
        {
            title: 'Task form',
            kicker: 'Build · Add task',
            body: 'Tasks are what mentees complete each week. Fill title, description, type, submission type, and estimated minutes.',
            caption: 'This is the Task form. Speak the title and description. Then choose type and submission type from the dropdowns.',
            speak: 'This is the Task form. Speak the title and description. Then choose type and submission type from the dropdowns.',
            form: 'task',
            highlight: 'title',
            filled: { title: true, description: true },
            fieldNote: 'Task form preview',
            durationSec: 9,
        },
        {
            title: 'Task: Type & submission',
            kicker: 'Build · Task details',
            body: 'Type can be task, reading, video, or practice. Submission can be none, text, file, or link. Mark required if mentees must complete it.',
            caption: 'Say the task type, for example task or reading. Then say the submission type, like text or file. Save when ready.',
            speak: 'Say the task type, for example task or reading. Then say the submission type, like text or file. Save when ready.',
            form: 'task',
            highlight: 'type',
            filled: { title: true, description: true, type: true, submission_type: true, estimated_minutes: true, is_required: true },
            fieldNote: 'Highlighting Type',
            durationSec: 9,
        },
        {
            title: 'MCQ form',
            kicker: 'Build · Add quiz question',
            body: 'MCQs check understanding. Fill topic, question, options A to D, explanation, and difficulty. Voice fill works on each option.',
            caption: 'Finally, the MCQ form. Speak the question and each option. You can also dictate the explanation and choose difficulty.',
            speak: 'Finally, the MCQ form. Speak the question and each option. You can also dictate the explanation and choose difficulty.',
            form: 'mcq',
            highlight: 'question',
            filled: { topic: true, question: true, option_0: true, option_1: true },
            fieldNote: 'MCQ form preview',
            durationSec: 10,
        },
        {
            title: 'You are ready',
            kicker: 'Full curriculum flow',
            body: 'Template → Months → Weeks → Tasks → MCQs. Use Guided voice for the whole form, or the mic on one field. Review, then save.',
            caption: 'That is the full flow. Build the template, then months, weeks, tasks, and MCQs. Speak clearly, review the fields, and save.',
            speak: 'That is the full flow. Build the template, then months, weeks, tasks, and MCQs. Speak clearly, review the fields, and save.',
            form: 'template',
            highlight: null,
            filled: { expertise_field: true, name: true, description: true, expertise_tags: true, icon: true, color: true, is_active: true },
            fieldNote: 'All set — start whenever you are ready',
            durationSec: 9,
        },
    ];

    function $(sel, root) {
        return (root || document).querySelector(sel);
    }

    function fmtTime(sec) {
        sec = Math.max(0, Math.floor(sec || 0));
        return Math.floor(sec / 60) + ':' + String(sec % 60).padStart(2, '0');
    }

    function totalSec() {
        return STEPS.reduce(function (sum, s) { return sum + (s.durationSec || 8); }, 0);
    }

    function stepStartSec(idx) {
        var t = 0;
        for (var i = 0; i < idx; i++) t += STEPS[i].durationSec || 8;
        return t;
    }

    function stepIndexAtSec(sec) {
        var t = 0;
        for (var i = 0; i < STEPS.length; i++) {
            var dur = STEPS[i].durationSec || 8;
            if (sec < t + dur) return i;
            t += dur;
        }
        return STEPS.length - 1;
    }

    function pickVoice(lang) {
        try {
            var voices = window.speechSynthesis.getVoices() || [];
            if (!voices.length) return null;
            return voices.find(function (v) {
                return (v.lang || '').toLowerCase().indexOf((lang || 'en-US').toLowerCase()) === 0
                    && /google|microsoft|natural|male|david|mark|guy|aria|jenny/i.test(v.name || '');
            }) || voices.find(function (v) {
                return (v.lang || '').toLowerCase().indexOf((lang || 'en').toLowerCase().slice(0, 2)) === 0;
            }) || null;
        } catch (e) {
            return null;
        }
    }

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderFormPreview(step) {
        var form = FORMS[step.form] || FORMS.template;
        var filled = step.filled || {};
        var highlight = step.highlight;
        var html = '';

        html += '<div class="va-form-card">';
        html += '<div class="va-form-card__head"><span class="va-form-card__badge">Form preview</span><strong>' + escapeHtml(form.title) + '</strong></div>';
        html += '<div class="va-form-card__voice"><span class="va-form-card__mic">' + MIC_SVG + '</span><div><p class="va-form-card__voice-title">Voice fill</p><p class="va-form-card__voice-hint">Speak to fill fields — Guided voice or field mics</p></div><span class="va-form-card__guide-btn">Guided voice</span></div>';
        html += '<div class="va-form-grid">';

        form.fields.forEach(function (field) {
            var isActive = highlight === field.key;
            var hasValue = !!filled[field.key];
            var value = hasValue ? (field.sample == null ? '' : String(field.sample)) : '';
            var cls = 'va-field' + (isActive ? ' is-active' : '') + (field.mic ? ' has-mic' : '');

            html += '<div class="' + cls + '" data-field-key="' + escapeHtml(field.key) + '">';
            html += '<label class="va-field__label">' + escapeHtml(field.label) + (field.required ? ' <span>*</span>' : '') + '</label>';

            if (field.type === 'textarea') {
                html += '<div class="va-field__control">';
                html += '<textarea class="va-field__input' + (field.mono ? ' is-mono' : '') + '" rows="' + (field.rows || 3) + '" readonly placeholder="' + escapeHtml(field.placeholder || '') + '">' + escapeHtml(value) + '</textarea>';
                if (field.mic) html += '<span class="va-field__mic">' + MIC_SVG + '</span>';
                html += '</div>';
            } else if (field.type === 'select') {
                html += '<div class="va-field__control">';
                html += '<select class="va-field__input" disabled>';
                (field.options || []).forEach(function (opt) {
                    var selected = hasValue && opt === field.sample ? ' selected' : (!hasValue && opt.indexOf('Select') !== -1 ? ' selected' : '');
                    html += '<option' + selected + '>' + escapeHtml(opt) + '</option>';
                });
                html += '</select>';
                html += '</div>';
            } else if (field.type === 'color') {
                html += '<div class="va-field__control va-field__color">';
                html += '<span class="va-field__swatch" style="background:' + escapeHtml(hasValue ? field.sample : '#4f46e5') + '"></span>';
                html += '<span class="va-field__color-text">' + escapeHtml(hasValue ? field.sample : '#4f46e5') + '</span>';
                html += '</div>';
            } else if (field.type === 'toggle') {
                html += '<div class="va-field__toggle' + (hasValue && field.sample ? ' is-on' : '') + '"><span class="va-field__toggle-knob"></span><span>' + (field.hint || (hasValue && field.sample ? 'On' : 'Off')) + '</span></div>';
            } else {
                html += '<div class="va-field__control">';
                html += '<input class="va-field__input' + (field.center ? ' is-center' : '') + '" type="text" readonly placeholder="' + escapeHtml(field.placeholder || '') + '" value="' + escapeHtml(value) + '">';
                if (field.mic) html += '<span class="va-field__mic">' + MIC_SVG + '</span>';
                html += '</div>';
            }

            if (field.hint && field.type !== 'toggle') {
                html += '<p class="va-field__hint">' + escapeHtml(field.hint) + '</p>';
            }
            html += '</div>';
        });

        html += '</div></div>';
        return html;
    }

    function init(modal) {
        if (!modal || modal.dataset.guideReady === '1') return;
        modal.dataset.guideReady = '1';

        var hasVideo = modal.getAttribute('data-has-video') === '1';
        var storageKey = modal.getAttribute('data-storage-key') || 'vedrix_voice_guide_skip_v1';
        var video = $('[data-guide-video]', modal);
        var langSelect = $('[data-guide-lang]', modal);
        var captionEl = $('[data-guide-caption]', modal);
        var titleEl = $('[data-guide-title]', modal);
        var kickerEl = $('[data-guide-kicker]', modal);
        var bodyEl = $('[data-guide-body]', modal);
        var fieldsEl = $('[data-guide-fields]', modal);
        var fieldNoteEl = $('[data-guide-field-note]', modal);
        var stepPill = $('[data-guide-step-pill]', modal);
        var progressEl = $('[data-guide-progress]', modal);
        var thumbEl = $('[data-guide-thumb]', modal);
        var seekEl = $('[data-guide-seek]', modal);
        var timeEl = $('[data-guide-time]', modal);
        var playBtn = $('[data-guide-play]', modal);
        var playIcon = $('[data-guide-play-icon]', modal);
        var pauseIcon = $('[data-guide-pause-icon]', modal);
        var replayBtn = $('[data-guide-replay]', modal);
        var backBtn = $('[data-guide-back]', modal);
        var forwardBtn = $('[data-guide-forward]', modal);
        var skipNext = $('[data-guide-skip-next]', modal);
        var startBtn = $('[data-guide-start]', modal);
        var liveDot = $('[data-guide-live-dot]', modal);

        var positionSec = 0;
        var index = 0;
        var spokenIndex = -1;
        var playing = false;
        var ended = false;
        var tickId = null;
        var lastTickAt = 0;
        var dragging = false;
        var speakToken = 0;
        var voicesPromise = null;

        function lang() {
            return (langSelect && langSelect.value) || 'en-US';
        }

        function setPlayingUi(on) {
            playing = !!on;
            if (playIcon) playIcon.classList.toggle('hidden', playing);
            if (pauseIcon) pauseIcon.classList.toggle('hidden', !playing);
            if (liveDot) {
                liveDot.classList.toggle('bg-emerald-400', playing);
                liveDot.classList.toggle('bg-slate-400', !playing);
            }
        }

        function stopSpeech() {
            speakToken += 1;
            try { window.speechSynthesis && window.speechSynthesis.cancel(); } catch (e) {}
        }

        function ensureVoicesReady() {
            if (!window.speechSynthesis) return Promise.resolve([]);
            if (voicesPromise) return voicesPromise;

            voicesPromise = new Promise(function (resolve) {
                var done = false;
                function finish() {
                    if (done) return;
                    done = true;
                    resolve(window.speechSynthesis.getVoices() || []);
                }

                var existing = window.speechSynthesis.getVoices() || [];
                if (existing.length) {
                    finish();
                    return;
                }

                if (typeof window.speechSynthesis.addEventListener === 'function') {
                    window.speechSynthesis.addEventListener('voiceschanged', finish, { once: true });
                } else {
                    window.speechSynthesis.onvoiceschanged = finish;
                }
                // Force Chrome to populate voices, then fall back.
                try { window.speechSynthesis.getVoices(); } catch (e) {}
                setTimeout(finish, 700);
            });

            return voicesPromise;
        }

        function queueUtterance(text, token, stepIndex) {
            var u = new SpeechSynthesisUtterance(text);
            u.lang = lang();
            u.rate = 0.92;
            var voice = pickVoice(u.lang);
            if (voice) u.voice = voice;

            var started = false;
            u.onstart = function () { started = true; };

            try {
                // Chrome can stay "paused" and silently drop the first speak.
                window.speechSynthesis.resume();
                window.speechSynthesis.speak(u);
                window.speechSynthesis.resume();
            } catch (e) {}

            // If the first cold-start utterance was eaten, retry once.
            setTimeout(function () {
                if (token !== speakToken || stepIndex !== index || !playing) return;
                if (started || window.speechSynthesis.speaking || window.speechSynthesis.pending) return;

                var retry = new SpeechSynthesisUtterance(text);
                retry.lang = u.lang;
                retry.rate = u.rate;
                if (voice) retry.voice = voice;
                try {
                    window.speechSynthesis.cancel();
                    window.speechSynthesis.resume();
                    window.speechSynthesis.speak(retry);
                    window.speechSynthesis.resume();
                } catch (e) {}
            }, 280);
        }

        function speakCurrentStep() {
            if (!window.speechSynthesis) return;
            var step = STEPS[index];
            if (!step) return;

            var stepIndex = index;
            var text = step.speak;
            spokenIndex = stepIndex;

            // Bump token and cancel any previous speech, then wait briefly.
            // Immediate cancel()+speak() is a known Chrome cold-start failure.
            var token = ++speakToken;
            try { window.speechSynthesis.cancel(); } catch (e) {}

            ensureVoicesReady().then(function () {
                if (token !== speakToken || stepIndex !== index || !playing) return;
                setTimeout(function () {
                    if (token !== speakToken || stepIndex !== index || !playing) return;
                    queueUtterance(text, token, stepIndex);
                }, 90);
            });
        }

        function setProgressVisual(pct) {
            pct = Math.max(0, Math.min(100, pct || 0));
            if (progressEl) progressEl.style.width = pct + '%';
            if (thumbEl) thumbEl.style.left = 'calc(' + pct + '% - 8px)';
            if (seekEl) seekEl.setAttribute('aria-valuenow', String(Math.round(pct)));
        }

        function syncInteractiveUi() {
            var total = totalSec();
            setProgressVisual(total ? (positionSec / total) * 100 : 0);
            if (timeEl) timeEl.textContent = fmtTime(positionSec) + ' / ' + fmtTime(total);
        }

        function renderStepContent() {
            var step = STEPS[index] || STEPS[0];
            if (titleEl) titleEl.textContent = step.title;
            if (kickerEl) kickerEl.textContent = step.kicker;
            if (bodyEl) bodyEl.textContent = step.body;
            if (captionEl) captionEl.textContent = step.caption;
            if (stepPill) stepPill.textContent = 'Step ' + (index + 1) + ' of ' + STEPS.length;
            if (fieldNoteEl) fieldNoteEl.textContent = step.fieldNote || 'Follow the highlighted field';
            if (fieldsEl) {
                fieldsEl.innerHTML = renderFormPreview(step);
                var active = fieldsEl.querySelector('.va-field.is-active');
                if (active) {
                    try { active.scrollIntoView({ block: 'nearest', behavior: 'smooth' }); } catch (e) {}
                }
            }
        }

        function applyStepFromPosition(shouldSpeak) {
            var nextIndex = stepIndexAtSec(Math.min(positionSec, Math.max(0, totalSec() - 0.01)));
            var changed = nextIndex !== index;
            index = nextIndex;
            renderStepContent();
            syncInteractiveUi();
            if (shouldSpeak && playing && (changed || spokenIndex !== index)) {
                speakCurrentStep();
            }
        }

        function stopTicker() {
            if (tickId) {
                clearInterval(tickId);
                tickId = null;
            }
        }

        function onEnded() {
            ended = true;
            positionSec = totalSec();
            index = STEPS.length - 1;
            stopTicker();
            stopSpeech();
            setPlayingUi(false);
            renderStepContent();
            syncInteractiveUi();
        }

        function tick() {
            if (!playing || dragging) return;
            var now = Date.now();
            var delta = (now - lastTickAt) / 1000;
            lastTickAt = now;
            if (delta < 0 || delta > 1) delta = 0.1;

            positionSec = Math.min(totalSec(), positionSec + delta);
            var nextIndex = stepIndexAtSec(Math.min(positionSec, Math.max(0, totalSec() - 0.01)));

            if (nextIndex !== index) {
                index = nextIndex;
                renderStepContent();
                speakCurrentStep();
            }

            syncInteractiveUi();
            if (positionSec >= totalSec()) onEnded();
        }

        function playInteractive() {
            if (ended || positionSec >= totalSec()) {
                ended = false;
                positionSec = 0;
                index = 0;
                spokenIndex = -1;
            }
            setPlayingUi(true);
            ended = false;
            stopTicker();

            // Wait for voices, then start clock + first sentence together.
            // Starting the ticker earlier made the first line appear while audio was still silent.
            ensureVoicesReady().then(function () {
                if (!playing) return;
                lastTickAt = Date.now();
                tickId = setInterval(tick, 100);
                applyStepFromPosition(true);
            });
        }

        function pauseInteractive() {
            setPlayingUi(false);
            stopTicker();
            stopSpeech();
            syncInteractiveUi();
        }

        function seekInteractive(sec, keepPlaying) {
            var total = totalSec();
            positionSec = Math.max(0, Math.min(total, sec));
            ended = positionSec >= total;
            var wasPlaying = keepPlaying != null ? keepPlaying : playing;

            stopTicker();
            stopSpeech();
            spokenIndex = -1;
            applyStepFromPosition(false);

            if (ended) {
                setPlayingUi(false);
                syncInteractiveUi();
                return;
            }

            if (wasPlaying) playInteractive();
            else {
                setPlayingUi(false);
                syncInteractiveUi();
            }
        }

        function skipBy(deltaSec) {
            seekInteractive(positionSec + deltaSec, playing || !ended);
        }

        function playVideo() {
            if (!video) return;
            if (video.ended) video.currentTime = 0;
            video.play().then(function () {
                setPlayingUi(true);
                ended = false;
            }).catch(function () {
                setPlayingUi(false);
            });
        }

        function pauseVideo() {
            if (!video) return;
            video.pause();
            setPlayingUi(false);
        }

        function syncVideoUi() {
            if (!video) return;
            var dur = video.duration || 0;
            var cur = video.currentTime || 0;
            setProgressVisual(dur ? (cur / dur) * 100 : 0);
            if (timeEl) timeEl.textContent = fmtTime(cur) + ' / ' + fmtTime(dur);
        }

        function seekVideo(sec, resume) {
            if (!video) return;
            var dur = video.duration || 0;
            video.currentTime = Math.max(0, Math.min(dur || 0, sec));
            syncVideoUi();
            if (resume) playVideo();
            else if (!playing) pauseVideo();
        }

        function ratioFromEvent(e) {
            var rect = seekEl.getBoundingClientRect();
            if (!rect.width) return 0;
            var clientX = e.touches && e.touches[0] ? e.touches[0].clientX : e.clientX;
            return Math.max(0, Math.min(1, (clientX - rect.left) / rect.width));
        }

        function seekFromEvent(e, resumeAfter) {
            var ratio = ratioFromEvent(e);
            if (hasVideo && video) {
                var dur = video.duration || 0;
                if (!dur) return;
                seekVideo(dur * ratio, resumeAfter);
                return;
            }
            seekInteractive(totalSec() * ratio, resumeAfter);
        }

        function primeSpeechEngine() {
            if (!window.speechSynthesis) return;
            ensureVoicesReady();
            try {
                // Unlock Chrome speech inside the user-click gesture (fixes silent first line).
                var warm = new SpeechSynthesisUtterance(' ');
                warm.volume = 0;
                warm.rate = 2;
                warm.lang = lang();
                window.speechSynthesis.speak(warm);
                window.speechSynthesis.cancel();
                window.speechSynthesis.resume();
            } catch (e) {}
        }

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            if (skipNext) skipNext.checked = localStorage.getItem(storageKey) === '1';

            if (hasVideo && video) {
                video.currentTime = 0;
                syncVideoUi();
                playVideo();
            } else {
                positionSec = 0;
                index = 0;
                spokenIndex = -1;
                ended = false;
                renderStepContent();
                syncInteractiveUi();
                primeSpeechEngine();
                playInteractive();
            }
        }

        function closeGuide() {
            pauseInteractive();
            pauseVideo();
            if (video) {
                try { video.pause(); video.currentTime = 0; } catch (e) {}
            }
            if (skipNext) localStorage.setItem(storageKey, skipNext.checked ? '1' : '0');
        }

        modal._openVoiceGuide = openModal;
        modal._closeVoiceGuideCleanup = closeGuide;

        playBtn && playBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (hasVideo && video) {
                if (playing) pauseVideo();
                else playVideo();
                return;
            }
            if (playing) pauseInteractive();
            else playInteractive();
        });

        replayBtn && replayBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (hasVideo && video) {
                video.currentTime = 0;
                playVideo();
                return;
            }
            ended = false;
            positionSec = 0;
            index = 0;
            spokenIndex = -1;
            playInteractive();
        });

        backBtn && backBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (hasVideo && video) {
                seekVideo((video.currentTime || 0) - 10, playing);
                return;
            }
            skipBy(-10);
        });

        forwardBtn && forwardBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (hasVideo && video) {
                seekVideo((video.currentTime || 0) + 10, playing);
                return;
            }
            skipBy(10);
        });

        function onSeekStart(e) {
            e.preventDefault();
            dragging = true;
            var resume = playing;
            if (hasVideo && video) pauseVideo();
            else pauseInteractive();
            seekFromEvent(e, false);

            function onMove(ev) {
                if (!dragging) return;
                ev.preventDefault();
                seekFromEvent(ev, false);
            }
            function onUp(ev) {
                dragging = false;
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
                document.removeEventListener('touchmove', onMove);
                document.removeEventListener('touchend', onUp);
                var point = ev.changedTouches ? ev.changedTouches[0] : ev;
                seekFromEvent(point, resume);
            }

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
            document.addEventListener('touchmove', onMove, { passive: false });
            document.addEventListener('touchend', onUp);
        }

        if (seekEl) {
            seekEl.addEventListener('mousedown', onSeekStart);
            seekEl.addEventListener('touchstart', onSeekStart, { passive: false });
            seekEl.addEventListener('keydown', function (e) {
                if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
                e.preventDefault();
                var delta = e.key === 'ArrowRight' ? 5 : -5;
                if (hasVideo && video) seekVideo((video.currentTime || 0) + delta, playing);
                else skipBy(delta);
            });
        }

        langSelect && langSelect.addEventListener('change', function () {
            if (!hasVideo && playing) speakCurrentStep();
        });

        skipNext && skipNext.addEventListener('change', function () {
            localStorage.setItem(storageKey, skipNext.checked ? '1' : '0');
        });

        startBtn && startBtn.addEventListener('click', function () {
            closeGuide();
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            var studio = document.querySelector('[data-voice-studio-shell]');
            if (studio && studio._openVoiceStudio) {
                studio._openVoiceStudio();
                return;
            }
            if (!document.querySelector('.tpl-modal.flex') && !document.querySelector('[data-voice-studio-shell]:not(.hidden)')) {
                document.body.style.overflow = '';
            }
            var addBtn = document.querySelector('[data-open-modal="add-template-modal"]');
            if (addBtn) addBtn.click();
        });

        if (video) {
            video.addEventListener('timeupdate', syncVideoUi);
            video.addEventListener('loadedmetadata', syncVideoUi);
            video.addEventListener('play', function () { setPlayingUi(true); ended = false; });
            video.addEventListener('pause', function () { if (!dragging) setPlayingUi(false); });
            video.addEventListener('ended', function () {
                ended = true;
                setPlayingUi(false);
                syncVideoUi();
            });
        }

        // Prefetch voices early so the opening sentence is not silent on cold start.
        ensureVoicesReady();

        positionSec = 0;
        index = 0;
        renderStepContent();
        syncInteractiveUi();
        setPlayingUi(false);
    }

    function boot() {
        var modal = document.querySelector('[data-voice-guide-modal]');
        if (!modal) return;
        init(modal);

        document.querySelectorAll('[data-open-voice-guide]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                modal._openVoiceGuide && modal._openVoiceGuide();
            });
        });

        var observer = new MutationObserver(function () {
            if (modal.classList.contains('hidden') && modal._closeVoiceGuideCleanup) {
                modal._closeVoiceGuideCleanup();
            }
        });
        observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})(window, document);
