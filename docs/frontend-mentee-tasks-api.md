# Mentee Tasks API (Frontend Integration)

Standalone tasks **outside curriculum** (not under Track → Month → Week). Mentors (or admin via web) assign tasks to a specific mentee. Mentee submits work; mentor reviews — same pattern as curriculum tasks.

---

## Auth

| Role | Header | Base path |
|------|--------|-----------|
| Mentee | `Authorization: Bearer {mentee_token}` | `/api/v1/mentee/tasks` |
| Mentor | `Authorization: Bearer {mentor_token}` | `/api/v1/mentor/tasks` |

Also send: `Accept: application/json`  
For file uploads: `Content-Type: multipart/form-data` (do **not** set JSON content-type).

---

## Concepts

| Term | Meaning |
|------|---------|
| **Task** | Assignment created for one mentee (`mentee_id`) and one reviewing mentor (`mentor_id`) |
| **Progress** | Submission / review state for that mentee on that task |
| **`ui_status`** | Ready-to-render status for UI chips (prefer this over raw DB fields) |
| **`submission_type`** | What the mentee must send: `none`, `text`, `file`, `link`, `pdf`, `video` |

### Status flow

```
pending  →  (mentee submits / marks complete)
             ├─ submission_type = none     → completed
             └─ other types                → awaiting_review
                                              ├─ mentor approves → completed
                                              └─ mentor rejects  → rejected  (mentee can resubmit)
```

| `ui_status` | Show to mentee | Show to mentor |
|-------------|----------------|----------------|
| `pending` | Not started — show submit / mark complete | Waiting for mentee |
| `in_progress` | Started | — |
| `awaiting_review` | “Waiting for mentor review” — disable resubmit until reviewed | Show Approve / Reject |
| `rejected` | Show feedback — allow resubmit | Reviewed (rejected) |
| `completed` | Done — hide submit | Done |

---

## Enums

### `type`

| Value | Label | Typical icon |
|-------|-------|--------------|
| `task` | Task | ✅ |
| `reading` | Reading | 📖 |
| `video` | Video | 🎬 |
| `project` | Project | 🚀 |
| `quiz` | Quiz | ❓ |
| `reflection` | Reflection | 💭 |

API also returns `type_label` and `type_icon`.

### `submission_type`

| Value | Mentee must send | Notes |
|-------|------------------|-------|
| `none` | Nothing | `POST .../submit` with empty body → marks **completed** immediately |
| `text` | `submission_text` | Required text (max 5000) |
| `link` | `submission_url` | Valid URL |
| `file` / `pdf` / `video` | `submission_file` | Multipart file; optional `submission_text` notes |

At least one of text / url / file is required when `submission_type !== none`.

### Attachments (on the task definition)

Mentor may attach reference files when creating/updating.

- Field: `attachments[]` (multipart, multiple)
- Max size: **10 MB** each
- Allowed: jpg, jpeg, png, gif, webp, bmp, svg, heic, heif, pdf, doc, docx, ppt, pptx, txt, mp4, mov, avi, webm, mpeg
- Served at: `GET /api/v1/media/mentee-tasks/{filename}` (use `attachments[].url` from API)

---

## Task object (shared shape)

Returned from list / show / create / update / submit.

```json
{
  "id": 12,
  "title": "Rewrite your LinkedIn About section",
  "description": "Use the STAR format. Keep under 200 words.",
  "type": "task",
  "type_label": "Task",
  "type_icon": "✅",
  "submission_type": "text",
  "submission_label": "Text",
  "attachments": [
    {
      "name": "brief.pdf",
      "path": "mentee-tasks/xyz.pdf",
      "url": "https://…/api/v1/media/mentee-tasks/xyz.pdf",
      "mime": "application/pdf",
      "size": 84200
    }
  ],
  "estimated_minutes": 30,
  "is_required": true,
  "is_active": true,
  "due_at": null,
  "mentee_id": 2,
  "mentor_id": 3,
  "created_by": 3,
  "mentee": {
    "id": 2,
    "name": "Vijay Kumar",
    "email": "vijay@example.com",
    "avatar_url": "https://…"
  },
  "mentor": {
    "id": 3,
    "name": "Raaz",
    "email": "mentor@example.com",
    "avatar_url": null
  },
  "ui_status": "awaiting_review",
  "progress": {
    "id": 401,
    "is_completed": false,
    "submission_status": "submitted",
    "submission_text": "Here is my draft…",
    "submission_url": null,
    "mentor_feedback": null,
    "reviewed_at": null,
    "completed_at": null,
    "updated_at": "2026-09-26 10:15:00"
  },
  "created_at": "2026-09-25 18:00:00"
}
```

`progress` is `null` until the mentee has submitted / completed once.

Pagination meta (list endpoints):

```json
"meta": {
  "current_page": 1,
  "last_page": 3,
  "total": 42
}
```

---

## Mentee app screens

### 1. Task list

`GET /api/v1/mentee/tasks`

- Returns only **active** tasks for the logged-in mentee (newest first).
- Use `ui_status` for chips; `mentor.name` for subtitle.

```http
GET /api/v1/mentee/tasks
Authorization: Bearer {mentee_token}
```

```json
{
  "status": true,
  "message": "Tasks fetched.",
  "data": [ /* Task objects */ ],
  "meta": { "current_page": 1, "last_page": 1, "total": 2 }
}
```

### 2. Task detail

`GET /api/v1/mentee/tasks/{id}`

- 404 if not owned by this mentee.
- Render description + `attachments[]` (open `url` in browser / WebView).
- If `progress.mentor_feedback` is set (after reject/approve), show it.

### 3. Submit / mark complete

`POST /api/v1/mentee/tasks/{id}/submit`

#### A) No submission (`submission_type === "none"`)

```http
POST /api/v1/mentee/tasks/12/submit
Authorization: Bearer {mentee_token}
Accept: application/json
```

Empty body is fine.

#### B) Text

```http
POST /api/v1/mentee/tasks/12/submit
Content-Type: application/json

{ "submission_text": "My answer here…" }
```

#### C) Link

```json
{ "submission_url": "https://docs.google.com/…" }
```

#### D) File / PDF / video (multipart)

```http
POST /api/v1/mentee/tasks/12/submit
Content-Type: multipart/form-data

submission_file: <binary>
submission_text: optional notes
```

**Success response**

```json
{
  "status": true,
  "message": "Submission received. Awaiting mentor review.",
  "data": {
    "completed": false,
    "awaiting_review": true,
    "submission_status": "submitted",
    "task": { /* full Task object */ }
  }
}
```

| Field | Use |
|-------|-----|
| `data.completed` | `true` → show success / completed state |
| `data.awaiting_review` | `true` → show “Waiting for mentor” |
| `data.task` | Refresh local task state |

**When to show the submit button**

| `ui_status` | Action |
|-------------|--------|
| `pending` / `rejected` / `in_progress` | Show submit (or Mark complete if `none`) |
| `awaiting_review` | Hide submit; show waiting state |
| `completed` | Hide submit; show completed |

After **reject**, `ui_status` becomes `rejected` — mentee can call submit again (overwrites previous submission, clears feedback).

---

## Mentor app screens

Mentor may only manage tasks where they are `mentor_id`, and only for mentees linked to them.

### 1. List my assigned tasks

`GET /api/v1/mentor/tasks`  
Optional filter: `?mentee_id=2`

```json
{
  "status": true,
  "data": [ /* Task objects with mentee + progress */ ],
  "meta": { "…" }
}
```

### 2. Create task

`POST /api/v1/mentor/tasks`

**JSON** (no files):

```json
{
  "mentee_id": 2,
  "title": "Rewrite your LinkedIn About section",
  "description": "Use STAR format.",
  "type": "task",
  "submission_type": "text",
  "estimated_minutes": 30,
  "due_at": "2026-10-05 18:00:00",
  "is_required": true,
  "is_active": true
}
```

| Field | Required | Notes |
|-------|----------|-------|
| `mentee_id` | yes | Must be a mentee linked to this mentor |
| `title` | yes | max 200 |
| `description` | no | |
| `type` | no | default `task` |
| `submission_type` | no | default `none` |
| `estimated_minutes` | no | 1–600 |
| `due_at` | no | datetime |
| `is_required` | no | default true |
| `is_active` | no | default true |
| `attachments[]` | no | multipart files |
| `mentor_id` | no | Ignored for mentor API — always set to self |

**Multipart example**

```
mentee_id: 2
title: Watch this prep video then reflect
submission_type: text
attachments[]: <file1>
attachments[]: <file2>
```

**201 response:** `{ "status": true, "message": "Task created.", "data": { /* Task */ } }`

**422** if mentee is not linked to mentor:  
`"Selected mentor is not linked to this mentee."` (same message used for link checks)

### 3. Show / update / delete

| Method | Path |
|--------|------|
| `GET` | `/api/v1/mentor/tasks/{id}` |
| `PUT` / `PATCH` / `POST` | `/api/v1/mentor/tasks/{id}` |
| `DELETE` | `/api/v1/mentor/tasks/{id}` |

Update accepts the same fields as create (all optional except when sending). For attachments:

| Flag | Effect |
|------|--------|
| `attachments[]` | Append new files (default) |
| `replace_attachments=1` | Delete old files, then store new uploads |
| `clear_attachments=1` | Remove all attachments |

Use **POST** (not PUT) when sending multipart files from mobile clients if PUT+multipart is unreliable.

### 4. Pending reviews inbox

`GET /api/v1/mentor/tasks/pending`  
Optional: `?mentee_id=2`

```json
{
  "status": true,
  "message": "Pending task submissions.",
  "data": [
    {
      "progress_id": 401,
      "submission_status": "submitted",
      "submission_text": "Here is my draft…",
      "submission_url": null,
      "submitted_at": "2026-09-26 10:15:00",
      "mentee": {
        "id": 2,
        "name": "Vijay Kumar",
        "email": "vijay@example.com",
        "avatar_url": "https://…"
      },
      "task": { /* Task object */ }
    }
  ],
  "meta": { "current_page": 1, "last_page": 1, "total": 1 }
}
```

Use `progress_id` for the review call. Open `submission_url` if present (file/link).

### 5. Approve / reject

`POST /api/v1/mentor/tasks/submissions/{progress_id}/review`

```json
{
  "submission_status": "approved",
  "mentor_feedback": "Great clarity. Tighten the last paragraph."
}
```

| Field | Required | Values |
|-------|----------|--------|
| `submission_status` | yes | `approved` \| `rejected` |
| `mentor_feedback` | no | max 5000 chars (recommended on reject) |

```json
{
  "status": true,
  "message": "Submission approved.",
  "data": {
    "progress_id": 401,
    "submission_status": "approved",
    "mentor_feedback": "Great clarity…",
    "task": { /* refreshed Task */ }
  }
}
```

- **approved** → mentee `ui_status` = `completed`  
- **rejected** → mentee `ui_status` = `rejected`; they can submit again  

---

## Suggested UI flows

### Mentee

1. **Tasks tab** → list (`GET /mentee/tasks`)  
2. Tap row → detail (`GET /mentee/tasks/{id}`)  
3. Show attachments as download/open links  
4. Footer CTA based on `submission_type` + `ui_status`  
5. On success, replace local task with `data.task`

### Mentor

1. **Tasks** → list; filter chips by mentee (`?mentee_id=`)  
2. **+ New task** form (title, mentee picker, submission type, optional attachments)  
3. **Reviews** badge → `GET /mentor/tasks/pending`  
4. Card: mentee name, task title, submission preview → Approve / Reject with optional feedback  

---

## Errors

| HTTP | When |
|------|------|
| `401` | Missing / invalid token |
| `403` | Mentor reviewing another mentor’s task |
| `404` | Task / submission not found or not owned |
| `422` | Validation (missing submission, invalid mentee link, bad file type/size) |

Error body shape:

```json
{
  "status": false,
  "message": "Please provide a submission (text, link, or file).",
  "errors": { "submission": ["…"] }
}
```

---

## Web (reference only)

Same feature on server-rendered UI (not required for mobile):

| Role | URL |
|------|-----|
| Mentee | `/mentee/tasks` |
| Mentor | `/mentor/tasks` |
| Admin | `/admin/mentee-tasks` (create + assign mentor; no public admin mobile API yet) |

---

