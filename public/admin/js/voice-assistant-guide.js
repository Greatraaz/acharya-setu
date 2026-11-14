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

    // One step = one field highlight + one matching spoken line (kept in sync).
    var STEPS = [
        {
            title: 'Hello, I am Adarsh',
            kicker: 'Adarsh · Curriculum guide',
            body: 'I will walk you through template, months, weeks, tasks, and MCQs — one field at a time.',
            caption: 'Hello. I am Adarsh. I will explain each curriculum field, one by one, while highlighting it on the form.',
            speak: 'Hello. I am Adarsh. I will explain each curriculum field, one by one, while highlighting it on the form.',
            form: 'template',
            highlight: null,
            filled: {},
            fieldNote: 'Starter template form',
        },
        {
            title: 'Template name',
            kicker: 'Template form · Required',
            body: 'Give the starter plan a short, clear name mentors will recognize. If that name is already used, pick another.',
            caption: 'First field: Template name. Keep it clear, like Engineering and Technology Path. If the name is already taken, choose a different one.',
            speak: 'First field: Template name. Keep it clear, like Engineering and Technology Path. If the name is already taken, choose a different one.',
            form: 'template',
            highlight: 'name',
            filled: { name: true },
            fieldNote: 'Highlighting Template name',
        },
        {
            title: 'Industry / domain',
            kicker: 'Template form · Required',
            body: 'Choose the industry next. Mentors only see templates that match their industry.',
            caption: 'Next field: Industry or domain. Choose the industry so the right mentors can see this template.',
            speak: 'Next field: Industry or domain. Choose the industry so the right mentors can see this template.',
            form: 'template',
            highlight: 'expertise_field',
            filled: { name: true, expertise_field: true },
            fieldNote: 'Highlighting Industry / domain',
        },
        {
            title: 'Icon',
            kicker: 'Template form · Optional',
            body: 'Pick a simple emoji icon for the template card.',
            caption: 'This is the Icon field. You can add an emoji so the template is easy to spot.',
            speak: 'This is the Icon field. You can add an emoji so the template is easy to spot.',
            form: 'template',
            highlight: 'icon',
            filled: { name: true, expertise_field: true, icon: true },
            fieldNote: 'Highlighting Icon',
        },
        {
            title: 'Accent color',
            kicker: 'Template form · Optional',
            body: 'Accent color styles the template card in lists.',
            caption: 'This is Accent color. Choose a color for the template card.',
            speak: 'This is Accent color. Choose a color for the template card.',
            form: 'template',
            highlight: 'color',
            filled: { name: true, expertise_field: true, icon: true, color: true },
            fieldNote: 'Highlighting Accent color',
        },
        {
            title: 'Description',
            kicker: 'Template form · Optional',
            body: 'A short description helps mentors understand the plan before they assign it.',
            caption: 'This is Description. Explain what the starter plan covers, or say skip.',
            speak: 'This is Description. Explain what the starter plan covers, or say skip.',
            form: 'template',
            highlight: 'description',
            filled: { name: true, expertise_field: true, icon: true, color: true, description: true },
            fieldNote: 'Highlighting Description',
        },
        {
            title: 'Skills',
            kicker: 'Template form · Optional',
            body: 'Skills are optional tags for admin reference.',
            caption: 'This is Skills. Add optional skill tags, or say skip.',
            speak: 'This is Skills. Add optional skill tags, or say skip.',
            form: 'template',
            highlight: 'expertise_tags',
            filled: { name: true, expertise_field: true, icon: true, color: true, description: true, expertise_tags: true, is_active: true },
            fieldNote: 'Highlighting Skills',
        },
        {
            title: 'Month number',
            kicker: 'Month form · Required',
            body: 'After creating the template, open Build and add months.',
            caption: 'Now the Month form. First choose the Month number, for example Month 1.',
            speak: 'Now the Month form. First choose the Month number, for example Month 1.',
            form: 'month',
            highlight: 'month_number',
            filled: { month_number: true },
            fieldNote: 'Highlighting Month number',
        },
        {
            title: 'Month theme',
            kicker: 'Month form · Optional',
            body: 'Theme is a short label like Foundation or Deep Dive.',
            caption: 'This is Theme. Say a short theme name, like Foundation.',
            speak: 'This is Theme. Say a short theme name, like Foundation.',
            form: 'month',
            highlight: 'theme',
            filled: { month_number: true, theme: true },
            fieldNote: 'Highlighting Theme',
        },
        {
            title: 'Month title',
            kicker: 'Month form · Required',
            body: 'Title is the main heading mentees see for the month.',
            caption: 'This is Month title. Speak a clear title for the month.',
            speak: 'This is Month title. Speak a clear title for the month.',
            form: 'month',
            highlight: 'title',
            filled: { month_number: true, theme: true, title: true },
            fieldNote: 'Highlighting Month title',
        },
        {
            title: 'Month description',
            kicker: 'Month form · Optional',
            body: 'Describe what the mentee should accomplish this month.',
            caption: 'This is Month description. Explain what the mentee will accomplish this month.',
            speak: 'This is Month description. Explain what the mentee will accomplish this month.',
            form: 'month',
            highlight: 'description',
            filled: { month_number: true, theme: true, title: true, description: true },
            fieldNote: 'Highlighting Month description',
        },
        {
            title: 'Learning outcomes',
            kicker: 'Month form · Optional',
            body: 'Learning outcomes appear as bullet points. Enter one outcome per line.',
            caption: 'This is Learning outcomes. Say one outcome at a time, one per line.',
            speak: 'This is Learning outcomes. Say one outcome at a time, one per line.',
            form: 'month',
            highlight: 'learning_outcomes',
            filled: { month_number: true, theme: true, title: true, description: true, learning_outcomes: true },
            fieldNote: 'Highlighting Learning outcomes',
        },
        {
            title: 'Milestone badge',
            kicker: 'Month form · Optional',
            body: 'The badge name is awarded when the month is fully completed.',
            caption: 'This is Milestone badge. Say the reward name mentees earn at one hundred percent.',
            speak: 'This is Milestone badge. Say the reward name mentees earn at one hundred percent.',
            form: 'month',
            highlight: 'milestone_badge',
            filled: { month_number: true, theme: true, title: true, description: true, learning_outcomes: true, milestone_badge: true },
            fieldNote: 'Highlighting Milestone badge',
        },
        {
            title: 'Week number',
            kicker: 'Week form · Required',
            body: 'Inside a month, add weeks for the weekly plan.',
            caption: 'Now the Week form. First choose the Week number.',
            speak: 'Now the Week form. First choose the Week number.',
            form: 'week',
            highlight: 'week_number',
            filled: { week_number: true },
            fieldNote: 'Highlighting Week number',
        },
        {
            title: 'Week title',
            kicker: 'Week form · Required',
            body: 'Week title is the weekly heading mentees see.',
            caption: 'This is Week title. Speak the title for this week.',
            speak: 'This is Week title. Speak the title for this week.',
            form: 'week',
            highlight: 'title',
            filled: { week_number: true, title: true },
            fieldNote: 'Highlighting Week title',
        },
        {
            title: 'Week focus',
            kicker: 'Week form · Optional',
            body: 'Focus is a short statement of what this week is about.',
            caption: 'This is Focus. Say the main focus for the week in one short line.',
            speak: 'This is Focus. Say the main focus for the week in one short line.',
            form: 'week',
            highlight: 'focus',
            filled: { week_number: true, title: true, focus: true },
            fieldNote: 'Highlighting Focus',
        },
        {
            title: 'Mentor guide',
            kicker: 'Week form · Private',
            body: 'Mentor guide is private coaching notes. Mentees do not see this.',
            caption: 'This is Mentor guide. Dictate private coaching notes for the mentor only.',
            speak: 'This is Mentor guide. Dictate private coaching notes for the mentor only.',
            form: 'week',
            highlight: 'mentor_guide',
            filled: { week_number: true, title: true, focus: true, mentor_guide: true },
            fieldNote: 'Highlighting Mentor guide',
        },
        {
            title: 'Intro video URL',
            kicker: 'Week form · Optional',
            body: 'Optional intro video link for the week.',
            caption: 'This is Intro video URL. Paste a video link if you have one, or leave it blank.',
            speak: 'This is Intro video U R L. Paste a video link if you have one, or leave it blank.',
            form: 'week',
            highlight: 'video_url',
            filled: { week_number: true, title: true, focus: true, mentor_guide: true, video_url: true },
            fieldNote: 'Highlighting Intro video URL',
        },
        {
            title: 'Task title',
            kicker: 'Task form · Required',
            body: 'Tasks are what mentees complete each week.',
            caption: 'Now the Task form. First fill Task title.',
            speak: 'Now the Task form. First fill Task title.',
            form: 'task',
            highlight: 'title',
            filled: { title: true },
            fieldNote: 'Highlighting Task title',
        },
        {
            title: 'Task description',
            kicker: 'Task form · Optional',
            body: 'Describe what the mentee should do.',
            caption: 'This is Task description. Explain the task clearly.',
            speak: 'This is Task description. Explain the task clearly.',
            form: 'task',
            highlight: 'description',
            filled: { title: true, description: true },
            fieldNote: 'Highlighting Task description',
        },
        {
            title: 'Task type',
            kicker: 'Task form · Required',
            body: 'Type can be task, reading, video, or practice.',
            caption: 'This is Type. Choose task, reading, video, or practice.',
            speak: 'This is Type. Choose task, reading, video, or practice.',
            form: 'task',
            highlight: 'type',
            filled: { title: true, description: true, type: true },
            fieldNote: 'Highlighting Type',
        },
        {
            title: 'Submission type',
            kicker: 'Task form · Required',
            body: 'Submission can be none, text, file, or link.',
            caption: 'This is Submission type. Choose none, text, file, or link.',
            speak: 'This is Submission type. Choose none, text, file, or link.',
            form: 'task',
            highlight: 'submission_type',
            filled: { title: true, description: true, type: true, submission_type: true },
            fieldNote: 'Highlighting Submission type',
        },
        {
            title: 'Estimated minutes',
            kicker: 'Task form · Optional',
            body: 'How long the task should take.',
            caption: 'This is Estimated minutes. Say how many minutes the task should take.',
            speak: 'This is Estimated minutes. Say how many minutes the task should take.',
            form: 'task',
            highlight: 'estimated_minutes',
            filled: { title: true, description: true, type: true, submission_type: true, estimated_minutes: true },
            fieldNote: 'Highlighting Est. Minutes',
        },
        {
            title: 'Required toggle',
            kicker: 'Task form',
            body: 'Mark whether the mentee must complete this task.',
            caption: 'This is the Required toggle. Turn it on if the mentee must complete this task.',
            speak: 'This is the Required toggle. Turn it on if the mentee must complete this task.',
            form: 'task',
            highlight: 'is_required',
            filled: { title: true, description: true, type: true, submission_type: true, estimated_minutes: true, is_required: true },
            fieldNote: 'Highlighting Required',
        },
        {
            title: 'MCQ topic',
            kicker: 'MCQ form',
            body: 'MCQs check understanding for the week.',
            caption: 'Now the MCQ form. First fill Topic.',
            speak: 'Now the M C Q form. First fill Topic.',
            form: 'mcq',
            highlight: 'topic',
            filled: { topic: true },
            fieldNote: 'Highlighting Topic',
        },
        {
            title: 'MCQ question',
            kicker: 'MCQ form · Required',
            body: 'Write the full quiz question.',
            caption: 'This is Question. Speak the full quiz question.',
            speak: 'This is Question. Speak the full quiz question.',
            form: 'mcq',
            highlight: 'question',
            filled: { topic: true, question: true },
            fieldNote: 'Highlighting Question',
        },
        {
            title: 'Option A',
            kicker: 'MCQ form · Required',
            body: 'Fill each answer choice. Start with Option A.',
            caption: 'This is Option A. Speak the first answer choice.',
            speak: 'This is Option A. Speak the first answer choice.',
            form: 'mcq',
            highlight: 'option_0',
            filled: { topic: true, question: true, option_0: true },
            fieldNote: 'Highlighting Option A',
        },
        {
            title: 'Option B',
            kicker: 'MCQ form · Required',
            body: 'Next answer choice.',
            caption: 'This is Option B. Speak the second answer choice.',
            speak: 'This is Option B. Speak the second answer choice.',
            form: 'mcq',
            highlight: 'option_1',
            filled: { topic: true, question: true, option_0: true, option_1: true },
            fieldNote: 'Highlighting Option B',
        },
        {
            title: 'Option C',
            kicker: 'MCQ form · Required',
            body: 'Third answer choice.',
            caption: 'This is Option C. Speak the third answer choice.',
            speak: 'This is Option C. Speak the third answer choice.',
            form: 'mcq',
            highlight: 'option_2',
            filled: { topic: true, question: true, option_0: true, option_1: true, option_2: true },
            fieldNote: 'Highlighting Option C',
        },
        {
            title: 'Option D',
            kicker: 'MCQ form · Required',
            body: 'Fourth answer choice.',
            caption: 'This is Option D. Speak the fourth answer choice.',
            speak: 'This is Option D. Speak the fourth answer choice.',
            form: 'mcq',
            highlight: 'option_3',
            filled: { topic: true, question: true, option_0: true, option_1: true, option_2: true, option_3: true },
            fieldNote: 'Highlighting Option D',
        },
        {
            title: 'MCQ explanation',
            kicker: 'MCQ form · Optional',
            body: 'Explain why the correct answer is right.',
            caption: 'This is Explanation. Say why the correct answer is right.',
            speak: 'This is Explanation. Say why the correct answer is right.',
            form: 'mcq',
            highlight: 'explanation',
            filled: { topic: true, question: true, option_0: true, option_1: true, option_2: true, option_3: true, explanation: true, difficulty: true },
            fieldNote: 'Highlighting Explanation',
        },
        {
            title: 'You are ready',
            kicker: 'Full curriculum flow',
            body: 'Template → Months → Weeks → Tasks → MCQs. Use Guided voice or field mics, review, then save.',
            caption: 'That is the full flow. Build template, months, weeks, tasks, and MCQs. Review each field, then save.',
            speak: 'That is the full flow. Build template, months, weeks, tasks, and MCQs. Review each field, then save.',
            form: 'template',
            highlight: null,
            filled: { expertise_field: true, name: true, description: true, expertise_tags: true, icon: true, color: true, is_active: true },
            fieldNote: 'All set — start whenever you are ready',
        },
    ];

    function estimateSpeechSec(text) {
        var words = String(text || '').trim().split(/\s+/).filter(Boolean).length;
        // Speak rate ~0.92; keep enough room so lines never cut off.
        return Math.max(5, Math.ceil(words / 2.0) + 2);
    }

    // Duration drives the progress bar; advancement is gated on speech finishing.
    STEPS.forEach(function (step) {
        step.durationSec = estimateSpeechSec(step.speak || step.caption);
    });

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
        var speechDoneFor = -1;
        var speechHoldUntil = 0;
        var advanceTimer = null;
        var stepStartedAt = 0;
        var speechKeepAliveId = null;

        function isSpeechBusy() {
            try {
                return !!(window.speechSynthesis && (window.speechSynthesis.speaking || window.speechSynthesis.pending));
            } catch (e) {
                return false;
            }
        }

        function clearAdvanceTimer() {
            if (advanceTimer) {
                clearTimeout(advanceTimer);
                advanceTimer = null;
            }
        }

        function startSpeechKeepAlive() {
            stopSpeechKeepAlive();
            // Chrome often auto-pauses speechSynthesis mid-utterance; nudge it awake.
            speechKeepAliveId = setInterval(function () {
                if (!playing || !window.speechSynthesis) return;
                try {
                    if (window.speechSynthesis.paused || window.speechSynthesis.speaking) {
                        window.speechSynthesis.resume();
                    }
                } catch (e) {}
            }, 250);
        }

        function stopSpeechKeepAlive() {
            if (speechKeepAliveId) {
                clearInterval(speechKeepAliveId);
                speechKeepAliveId = null;
            }
        }

        function goToStep(nextIndex, shouldSpeak) {
            nextIndex = Math.max(0, Math.min(STEPS.length - 1, nextIndex));
            index = nextIndex;
            positionSec = stepStartSec(index);
            spokenIndex = -1;
            speechDoneFor = -1;
            clearAdvanceTimer();
            renderStepContent();
            syncInteractiveUi();
            if (shouldSpeak && playing) speakCurrentStep();
        }

        function advanceAfterSpeech(fromIndex) {
            if (!playing || fromIndex !== index) return;
            if (index >= STEPS.length - 1) {
                onEnded();
                return;
            }
            // Move highlight + caption together with the next spoken line.
            goToStep(index + 1, true);
        }

        function markSpeechDone(stepIndex, token) {
            if (token !== speakToken) return;
            if (stepIndex !== index) return;
            if (speechDoneFor === stepIndex) return;
            speechDoneFor = stepIndex;
            // Snap progress to end of this step, then continue to the next field.
            positionSec = stepStartSec(index + 1);
            syncInteractiveUi();
            clearAdvanceTimer();
            advanceTimer = setTimeout(function () {
                advanceTimer = null;
                if (!playing || speakToken !== token || index !== stepIndex) return;
                advanceAfterSpeech(stepIndex);
            }, 450);
        }

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
            speechHoldUntil = 0;
            clearAdvanceTimer();
            try { window.speechSynthesis && window.speechSynthesis.cancel(); } catch (e) {}
            // Chrome can stay stuck in a paused/muted state after cancel.
            try { window.speechSynthesis && window.speechSynthesis.resume(); } catch (e) {}
        }

        function unlockSpeechEngine() {
            if (!window.speechSynthesis) return;
            try {
                // Resume in the user-gesture stack so Chrome unmutes after pause/cancel.
                window.speechSynthesis.resume();
            } catch (e) {}
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
            var voice = pickVoice(lang());
            var started = false;
            var settling = false;

            function finish(fromError) {
                if (settling) return;
                if (token !== speakToken || stepIndex !== index) return;
                settling = true;
                markSpeechDone(stepIndex, token);
            }

            function speakOnce(isRetry) {
                if (token !== speakToken || stepIndex !== index || !playing) return;
                var u = new SpeechSynthesisUtterance(text);
                u.lang = lang();
                u.rate = 0.92;
                u.volume = 1;
                if (voice) u.voice = voice;

                u.onstart = function () {
                    started = true;
                    try { window.speechSynthesis.resume(); } catch (e) {}
                };
                u.onend = function () { finish(false); };
                u.onerror = function (ev) {
                    // Ignore cancel/interrupt from our own retry handoff — do not advance early.
                    var err = (ev && ev.error) || '';
                    if (err === 'interrupted' || err === 'canceled' || err === 'cancelled') return;
                    if (!started && !isRetry) return;
                    finish(true);
                };

                try {
                    // Chrome can stay "paused" and silently drop speak() after pause/cancel.
                    window.speechSynthesis.resume();
                    window.speechSynthesis.speak(u);
                    window.speechSynthesis.resume();
                } catch (e) {
                    finish(true);
                }
            }

            speakOnce(false);

            // Retry if the utterance never starts (common after pause → play).
            function retryIfSilent(delay) {
                setTimeout(function () {
                    if (token !== speakToken || stepIndex !== index || !playing || settling) return;
                    if (started || window.speechSynthesis.speaking || window.speechSynthesis.pending) return;
                    try {
                        window.speechSynthesis.cancel();
                        window.speechSynthesis.resume();
                    } catch (e) {}
                    speakOnce(true);
                }, delay);
            }
            retryIfSilent(320);
            retryIfSilent(900);
        }

        function speakCurrentStep() {
            var step = STEPS[index];
            if (!step) return;

            var stepIndex = index;
            var text = String(step.speak || step.caption || '').trim();
            spokenIndex = stepIndex;
            speechDoneFor = -1;
            stepStartedAt = Date.now();
            speechHoldUntil = stepStartedAt + (estimateSpeechSec(text) * 1000) + 3000;
            clearAdvanceTimer();
            // Highlight + caption are already on this step; snap progress to its start.
            positionSec = stepStartSec(stepIndex);
            renderStepContent();
            syncInteractiveUi();

            // Bump token and cancel any previous speech, then wait briefly.
            // Immediate cancel()+speak() is a known Chrome cold-start failure.
            var token = ++speakToken;
            if (!window.speechSynthesis || !text) {
                markSpeechDone(stepIndex, token);
                return;
            }
            try { window.speechSynthesis.cancel(); } catch (e) {}
            try { window.speechSynthesis.resume(); } catch (e) {}

            ensureVoicesReady().then(function () {
                if (token !== speakToken || stepIndex !== index || !playing) return;
                setTimeout(function () {
                    if (token !== speakToken || stepIndex !== index || !playing) return;
                    queueUtterance(text, token, stepIndex);
                }, 140);
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
            clearAdvanceTimer();
            stopSpeechKeepAlive();
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

            // Progress within the current field only; step changes when speech ends.
            var start = stepStartSec(index);
            var dur = (STEPS[index] && STEPS[index].durationSec) || 6;
            var elapsed = stepStartedAt ? ((now - stepStartedAt) / 1000) : 0;
            var local = Math.min(dur * 0.96, Math.max(0, elapsed));
            positionSec = Math.min(totalSec(), start + local);

            // Keep audio alive if Chrome auto-paused mid line.
            try {
                if (window.speechSynthesis && window.speechSynthesis.paused) {
                    window.speechSynthesis.resume();
                }
            } catch (e) {}

            // Safety net: if Chrome never fires onend, advance after hold window.
            if (speechDoneFor !== index && now > speechHoldUntil && !isSpeechBusy()) {
                markSpeechDone(index, speakToken);
            }

            syncInteractiveUi();
        }

        function playInteractive() {
            if (ended || positionSec >= totalSec()) {
                ended = false;
                positionSec = 0;
                index = 0;
                spokenIndex = -1;
            }
            // Unlock inside the click gesture so Chrome is not muted after pause.
            unlockSpeechEngine();
            setPlayingUi(true);
            ended = false;
            stopTicker();
            startSpeechKeepAlive();

            // Force current line to speak again after pause (spokenIndex was cleared).
            spokenIndex = -1;
            speechDoneFor = -1;

            // Wait for voices, then start clock + current sentence together.
            ensureVoicesReady().then(function () {
                if (!playing) return;
                lastTickAt = Date.now();
                stepStartedAt = Date.now();
                tickId = setInterval(tick, 100);
                applyStepFromPosition(true);
                // Extra safety: if applyStep skipped speak, force it.
                if (spokenIndex !== index) speakCurrentStep();
            });
        }

        function pauseInteractive() {
            setPlayingUi(false);
            stopTicker();
            stopSpeechKeepAlive();
            stopSpeech();
            // So resume always re-speaks the current step instead of staying mute.
            spokenIndex = -1;
            speechDoneFor = -1;
            syncInteractiveUi();
        }

        function seekInteractive(sec, keepPlaying) {
            var total = totalSec();
            positionSec = Math.max(0, Math.min(total, sec));
            ended = positionSec >= total - 0.05;
            var wasPlaying = keepPlaying != null ? keepPlaying : playing;
            var nextIndex = stepIndexAtSec(Math.min(positionSec, Math.max(0, total - 0.01)));

            stopTicker();
            stopSpeech();
            spokenIndex = -1;
            speechDoneFor = -1;
            index = nextIndex;
            // Snap to the start of that field so highlight + voice stay aligned.
            positionSec = stepStartSec(index);
            renderStepContent();
            syncInteractiveUi();

            if (ended) {
                stopSpeechKeepAlive();
                setPlayingUi(false);
                return;
            }

            if (wasPlaying) {
                unlockSpeechEngine();
                setPlayingUi(true);
                startSpeechKeepAlive();
                lastTickAt = Date.now();
                stepStartedAt = Date.now();
                tickId = setInterval(tick, 100);
                speakCurrentStep();
            } else {
                stopSpeechKeepAlive();
                setPlayingUi(false);
            }
        }

        function skipBy(deltaSec) {
            // Jump by whole fields so highlight + voice stay paired.
            var dir = deltaSec >= 0 ? 1 : -1;
            var next = Math.max(0, Math.min(STEPS.length - 1, index + dir));
            var wasPlaying = playing;
            stopTicker();
            stopSpeech();
            ended = false;
            goToStep(next, false);
            if (wasPlaying) {
                unlockSpeechEngine();
                setPlayingUi(true);
                startSpeechKeepAlive();
                lastTickAt = Date.now();
                stepStartedAt = Date.now();
                tickId = setInterval(tick, 100);
                speakCurrentStep();
            } else {
                stopSpeechKeepAlive();
            }
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
            ensureVoicesReady();
            unlockSpeechEngine();
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
            if (playing) {
                pauseInteractive();
            } else {
                // Must unlock speech in this same click, before async play work.
                unlockSpeechEngine();
                playInteractive();
            }
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
            stopSpeech();
            goToStep(0, false);
            unlockSpeechEngine();
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
            if (hasVideo) return;
            if (playing) {
                unlockSpeechEngine();
                speakCurrentStep();
            } else {
                // Keep caption language in sync even when paused.
                renderStepContent();
            }
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
