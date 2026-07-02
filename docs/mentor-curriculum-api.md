# Mentor Curriculum API

Base URL: `{{baseUrl}} = http://127.0.0.1:8000/api/v1`  
Auth: `Authorization: Bearer {{mentorToken}}`  
Headers: `Accept: application/json`

All endpoints below are under:
`/mentor/curriculum/*`

---

## 1) List Tracks

`GET /mentor/curriculum/tracks`  
`GET /mentor/curriculum/tracks?mentee_id=5`

Returns mentor tracks. Optional `mentee_id` filters by mentee.

Response:

```json
{
  "status": true,
  "statuscode": 200,
  "tracks": [
    {
      "id": 1,
      "mentee_id": 5,
      "mentor_id": 8,
      "name": "Frontend Engineering Track",
      "slug": "frontend-engineering-track-5",
      "description": "Personalized FE roadmap",
      "is_active": true,
      "sort_order": 1
    }
  ]
}
```

## 2) Create Track

`POST /mentor/curriculum/tracks`

Body:

| Field | Type | Required | Rules |
|---|---|---|---|
| `mentee_id` | integer | Yes | must be mentee user ID |
| `name` | string | Yes | max 100 |
| `description` | string | No | nullable |
| `is_active` | boolean | No | default true |
| `sort_order` | integer | No | default 0 |

Example:

```json
{
  "mentee_id": 5,
  "name": "Frontend Engineering Track",
  "description": "Personalized FE roadmap",
  "is_active": true,
  "sort_order": 1
}
```

Response (201):

```json
{
  "status": true,
  "statuscode": 201,
  "message": "Track created.",
  "track": {
    "id": 1,
    "mentee_id": 5,
    "mentor_id": 8,
    "name": "Frontend Engineering Track"
  }
}
```

---

## 3) List Months For Track

`GET /mentor/curriculum/tracks/{track}/months`

Path params:
- `track` (integer)

Response:

```json
{
  "status": true,
  "statuscode": 200,
  "track_id": 1,
  "months": [
    {
      "id": 11,
      "stream_id": 1,
      "mentee_id": 5,
      "month_number": 1,
      "title": "Month 1 - Foundations",
      "theme": "Web Basics",
      "description": "HTML, CSS, JS fundamentals",
      "learning_outcomes": ["Build static pages", "Understand JS basics"],
      "is_active": true,
      "sort_order": 1,
      "weeks_count": 4
    }
  ]
}
```

## 4) Create Month

`POST /mentor/curriculum/tracks/{track}/months`

Body:

| Field | Type | Required | Rules |
|---|---|---|---|
| `mentee_id` | integer | Yes | must match track mentee |
| `month_number` | integer | Yes | 1-12, unique per track |
| `title` | string | Yes | max 200 |
| `theme` | string | No | max 100 |
| `description` | string | No | nullable |
| `learning_outcomes` | array | No | string[] |
| `is_active` | boolean | No | default true |
| `sort_order` | integer | No | default month_number |

Example:

```json
{
  "mentee_id": 5,
  "month_number": 1,
  "title": "Month 1 - Foundations",
  "theme": "Web Basics",
  "description": "HTML, CSS, JS fundamentals",
  "learning_outcomes": ["Build static pages", "Understand JS basics"],
  "is_active": true
}
```

## 5) Update Month

`PATCH /mentor/curriculum/months/{month}`

Body: any create fields (all optional).

## 6) Delete Month

`DELETE /mentor/curriculum/months/{month}`

Deletes month + underlying weeks/tasks/mcqs and related progress.

---

## 7) List Weeks For Month

`GET /mentor/curriculum/months/{month}/weeks`

Response includes `tasks_count` and `mcqs_count`.

```json
{
  "status": true,
  "statuscode": 200,
  "month_id": 11,
  "weeks": [
    {
      "id": 21,
      "month_id": 11,
      "mentee_id": 5,
      "week_number": 1,
      "title": "Week 1 - HTML/CSS",
      "focus": "Page structure and styling",
      "is_active": true,
      "sort_order": 1,
      "tasks_count": 3,
      "mcqs_count": 2
    }
  ]
}
```

## 8) Create Week

`POST /mentor/curriculum/months/{month}/weeks`

Body:

| Field | Type | Required | Rules |
|---|---|---|---|
| `mentee_id` | integer | Yes | must match month mentee |
| `week_number` | integer | Yes | 1-52, unique per month |
| `title` | string | Yes | max 200 |
| `focus` | string | No | nullable |
| `description` | string | No | alias for focus |
| `is_active` | boolean | No | default true |
| `sort_order` | integer | No | default week_number |

## 9) Update Week

`PATCH /mentor/curriculum/weeks/{week}`

## 10) Delete Week

`DELETE /mentor/curriculum/weeks/{week}`

Deletes week + underlying tasks/mcqs and related progress.

---

## 11) List Tasks For Week

`GET /mentor/curriculum/weeks/{week}/tasks`  
`GET /mentor/curriculum/weeks/{week}/tasks?mentee_id=5`

If `mentee_id` is sent, each task includes `is_completed`.

## 12) Create Task

`POST /mentor/curriculum/weeks/{week}/tasks`

Body:

| Field | Type | Required | Rules |
|---|---|---|---|
| `mentee_id` | integer | Yes | must match week mentee |
| `plan_id` | integer | Yes | must exist in plans |
| `title` | string | Yes | max 200 |
| `description` | string | No | nullable |
| `type` | string | No | `task|reading|video|project|quiz|reflection` |
| `submission_type` | string | No | `none|text|file|link|pdf|video` |
| `is_required` | boolean | No | default true |
| `is_active` | boolean | No | default true |
| `attachments` | array | No | JSON array if no files |

Also supports file upload with multipart key `attachments[]`.

## 13) Update Task

`PATCH /mentor/curriculum/tasks/{task}` (JSON updates)  
`POST /mentor/curriculum/tasks/{task}` (multipart/file updates)

Optional progress update fields:
- `mentee_id`
- `is_completed`

## 14) Delete Task

`DELETE /mentor/curriculum/tasks/{task}`

---

## 15) List MCQs For Week

`GET /mentor/curriculum/weeks/{week}/mcqs`  
`GET /mentor/curriculum/weeks/{week}/mcqs?mentee_id=5`

## 16) Create MCQ

`POST /mentor/curriculum/weeks/{week}/mcqs`

Body:

| Field | Type | Required | Rules |
|---|---|---|---|
| `mentee_id` | integer | Yes | must match week mentee |
| `question` | string | Yes | max 2000 |
| `options` | array | Yes | exactly 4 strings |
| `correct_option` | integer | Yes | `1-4` |
| `explanation` | string | No | nullable |
| `difficulty` | string | No | `easy|medium|hard` |
| `points` | integer | No | default 1 |
| `is_active` | boolean | No | default true |
| `order_index` | integer | No | default 0 |

Example:

```json
{
  "mentee_id": 5,
  "question": "Which HTML tag is used for the largest heading?",
  "options": ["<h1>", "<h2>", "<head>", "<title>"],
  "correct_option": 1,
  "explanation": "h1 is the largest heading tag.",
  "difficulty": "easy",
  "points": 1
}
```

Response returns both:
- `correct_index` (0-based, DB)
- `correct_option` (1-based, frontend friendly)

## 17) Update MCQ

`PATCH /mentor/curriculum/mcqs/{mcq}`

All fields optional. If `mentee_id` sent, it must match week mentee.

## 18) Delete MCQ

`DELETE /mentor/curriculum/mcqs/{mcq}`

Also removes related `student_curriculum_progress` (item_type=`mcq`).

---

## 19) List Supporting Materials For Week

`GET /mentor/curriculum/weeks/{week}/supporting-materials`  
`GET /mentor/curriculum/weeks/{week}/supporting-materials?mentee_id=5`

## 20) Create Supporting Material

`POST /mentor/curriculum/weeks/{week}/supporting-materials`

Body:

| Field | Type | Required | Rules |
|---|---|---|---|
| `mentee_id` | integer | Yes | must match week mentee |
| `type` | string | Yes | `pdf|doc|image|videolink|ppt` |
| `title` | string | No | nullable |
| `sort_order` | integer | No | default 0 |
| `is_active` | boolean | No | default true |
| `link` | string(url) | Yes if `videolink` | required for video link |
| `file` | file | Yes for file types | required for `pdf|doc|image|ppt` |

## 21) Update Supporting Material

`PATCH /mentor/curriculum/supporting-materials/{material}` (JSON)  
`POST /mentor/curriculum/supporting-materials/{material}` (multipart file update)

## 22) Delete Supporting Material

`DELETE /mentor/curriculum/supporting-materials/{material}`

---

## Route Param Rules

All ids are numeric-constrained in routes (`whereNumber`) for:
- `track`, `month`, `week`, `task`, `mcq`, `material`

Non-numeric ids return 404.

---

## Suggested Frontend Variables

- `baseUrl`
- `mentorToken`
- `menteeId`
- `trackId`
- `monthId`
- `weekId`
- `taskId`
- `mcqId`
- `materialId`
- `planId`

