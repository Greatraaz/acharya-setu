# Admin Curriculum Voice Assistant

**Product:** Vedrix Admin  
**Audience:** Developers maintaining curriculum admin screens  
**Scope:** Browser speech fill + Adarsh guided walkthrough + conversational template creation

---

## 1. Overview

The curriculum admin Voice feature lets admins fill forms by speaking instead of typing. It has three layers:

| Layer | What it does |
|-------|----------------|
| **Voice fill (forms)** | Guided voice + per-field mics on template / month / week / task / MCQ forms |
| **Adarsh walkthrough** | Video-style guide explaining every form with live field previews |
| **Create with Adarsh** | Chat-style assistant that asks questions and creates a starter template |

All speech runs **in the browser** (Web Speech API). No audio is uploaded to the server.

**Browser support:** Chrome or Edge on `localhost` or HTTPS. Microphone permission required for dictation.

---

## 2. Architecture

```
Admin Curriculum pages
├── voice-studio.blade.php      → Voice fill UI panel
├── voice-mic.blade.php         → Per-field mic button
├── voice-dictation.js          → Recognition + guided step engine
│
├── voice-assistant-guide.blade.php  → Adarsh walkthrough modal
├── voice-assistant-guide.js         → Timeline player + TTS + form previews
│
├── voice-assistant-studio.blade.php → Conversational create UI
└── voice-assistant-studio.js        → Chat Q&A → POST template store
```

### Key files

| File | Role |
|------|------|
| `public/admin/js/voice-dictation.js` | Core speech recognition, guided steps, field mics (`window.VedrixVoice`) |
| `resources/views/admin/curriculum/partials/voice-studio.blade.php` | Shared Voice fill panel (`data-voice-steps`) |
| `resources/views/admin/curriculum/partials/voice-mic.blade.php` | Field mic button (`data-dictate-for`) |
| `public/admin/js/voice-assistant-guide.js` | Adarsh walkthrough player |
| `resources/views/admin/curriculum/partials/voice-assistant-guide.blade.php` | Walkthrough modal markup |
| `public/admin/js/voice-assistant-studio.js` | Conversational template creator |
| `resources/views/admin/curriculum/partials/voice-assistant-studio.blade.php` | Chat studio shell + hidden create form |
| `public/admin/media/voice-assistant-guide.mp4` | Optional real video (if present, replaces interactive guide) |

---

## 3. Voice fill on forms

### 3.1 How a form is wired

1. Include the studio panel and pass step config:

```php
@include('admin.curriculum.partials.voice-studio', [
    'voiceTitle' => 'Voice fill',
    'voiceHint'  => 'Speak to fill the fields below…',
    'voiceSteps' => [
        ['key' => 'name', 'label' => 'Template name', 'prompt' => 'Say the template name.', 'type' => 'text'],
        // ...
    ],
])
```

2. Mark inputs with `data-voice-field="{key}"` matching each step `key`.
3. Optionally add a field mic:

```php
@include('admin.curriculum.partials.voice-mic', ['for' => 'name', 'label' => 'Dictate template name'])
```

4. Load the script (cache-busted):

```html
<script src="{{ asset('admin/js/voice-dictation.js') }}?v={{ @filemtime(...) }}"></script>
```

On `DOMContentLoaded`, `VedrixVoice.bootAll()` finds every `[data-voice-studio]` and initializes it.

### 3.2 Guided voice flow

1. Admin clicks **Guided voice**.
2. JS reads steps from `data-voice-steps` (JSON from `@json($voiceSteps)`).
3. For each step:
   - UI shows `Step N · {label}`
   - Browser TTS speaks the full field name + prompt
   - Mic listens
   - Final transcript is written into `[data-voice-field="{key}"]`
4. Admin can **Skip**, or say `skip` / `skip this`.
5. For `<select>` fields, transcript is fuzzy-matched to an `<option>` (`VedrixVoice.matchOption`).

Step object shape:

```js
{
  key: 'expertise_field',      // matches data-voice-field
  label: 'Industry / domain',  // shown in UI + spoken
  prompt: 'Say the industry…', // spoken instruction
  type: 'text' | 'select',
  append: true,   // optional: append to existing value
  newline: true,  // optional: append on new line
}
```

### 3.3 Field mics

Each `[data-dictate-for]` button opens a one-shot recognition session and fills only that field. Useful when the admin wants to re-dictate a single input.

### 3.4 Important implementation details

- **Steps encoding:** use `@json($voiceSteps)` in the attribute. Do **not** wrap with `e(json_encode(...))` inside `{{ }}` (double-escaping breaks `JSON.parse` → “No guided steps configured”).
- **TTS timing:** listening starts only after the spoken prompt finishes, so field names are not cut off.
- **Announcements:** always lead with the full label (`Next field: Industry or domain. …`). `/` is spoken as `or`.

### 3.5 Where Voice fill is enabled

- Starter templates (`templates/_fields.blade.php`)
- Months
- Weeks (settings + add week)
- Tasks
- MCQs
- Catalog / streams (name + description)

---

## 4. Adarsh walkthrough (guide video)

Opened from **AI Video Guide** (inside Create with Adarsh) or `[data-open-voice-guide]`.

### 4.1 What users see

- Host **Adarsh** explains the full curriculum builder
- Steps cover: **template → month → week → task → MCQ**
- Each step shows a **live form preview** styled like the real admin forms (inputs, selects, textareas, mics, Guided voice bar)
- Active field is highlighted
- Caption bar + YouTube-style controls:
  - Play / Pause
  - Back / Forward (~10s)
  - Scrubbable progress bar
  - Replay
  - Time `current / total`

### 4.2 How playback works

Interactive mode (default when no MP4 file exists):

1. Each step has a fixed `durationSec`.
2. A single timeline clock (`positionSec`) drives progress — not speech `onend`.
3. When the clock enters a new step, UI updates and TTS speaks that step’s script.
4. Seek / back / forward jump `positionSec` and re-sync step + speech.

Optional real video:

- Place file at `public/admin/media/voice-assistant-guide.mp4`
- Modal detects it via PHP `is_file(...)` and uses a normal `<video>` element with the same controls

### 4.3 Cold-start audio fix

Chrome often drops the **first** TTS utterance. Mitigations in `voice-assistant-guide.js`:

1. Prefetch voices (`ensureVoicesReady`)
2. Unlock speech inside the open click (`primeSpeechEngine`)
3. Short delay after `cancel()` before `speak()`
4. Retry once if the utterance never starts
5. Start the timeline only after voices are ready

### 4.4 CTA

**Start with Adarsh** closes the guide and opens the conversational studio (or New Template modal as fallback).

---

## 5. Create with Adarsh (conversational studio)

Opened from the templates page **Voice Assistant** button (`data-open-voice-studio`).

### 5.1 Flow

Chat asks one question at a time:

1. Industry / domain (required) — chips + fuzzy match to `MentorIndustry::all()`
2. Template name (required)
3. Description (optional / skip)
4. Skills (optional / skip)
5. Confirm summary → **Create template** or **Restart**

Input options: mic, typed text, suggestion chips, Explain, Skip, Repeat.

### 5.2 Submit

On confirm, a hidden form posts to:

`POST admin/curriculum/templates` → `CurriculumController@storeTemplate`

Fields: `expertise_field`, `name`, `description`, `expertise_tags`, `icon`, `color`, `is_active` (+ CSRF).

---

## 6. APIs used (browser only)

| API | Used for |
|-----|----------|
| `webkitSpeechRecognition` / `SpeechRecognition` | Dictation (forms + chat) |
| `speechSynthesis` + `SpeechSynthesisUtterance` | Adarsh / guided prompts |

No backend speech endpoints. Template create uses the existing Laravel web route.

---

## 7. Adding Voice fill to a new admin form

1. Define `$voiceSteps` with `key`, `label`, `prompt`, `type`.
2. `@include` `voice-studio` with those steps.
3. Add `data-voice-field` on each target input/select/textarea.
4. Add `voice-mic` partials where useful.
5. Include `voice-dictation.js` once on the page.
6. Verify steps appear in DevTools as valid JSON on `data-voice-steps`.

---

## 8. Testing checklist

- [ ] Chrome/Edge on localhost or HTTPS
- [ ] Mic permission allowed
- [ ] Guided voice fills each field in order; Skip works
- [ ] Select fields match spoken industry / type names
- [ ] Field mic updates only that field
- [ ] Adarsh guide: first sentence has audio on **first** open
- [ ] Progress bar seek + back/forward work
- [ ] Form previews show real fields for template/month/week/task/MCQ
- [ ] Create with Adarsh creates a template and redirects with success
- [ ] Closing modals stops mic + TTS

---

## 9. Known limits

- Speech quality depends on browser/OS voices and mic quality.
- Recognition works best in English (UI also offers `en-IN` / `en-GB` / `en-US`).
- Safari / Firefox support for Web Speech is limited or incomplete.
- Interactive guide is a narrated walkthrough, not a recorded avatar MP4 (unless you add `voice-assistant-guide.mp4`).
