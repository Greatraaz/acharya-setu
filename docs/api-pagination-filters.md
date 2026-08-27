

## Common query parameters

| Parameter | Default | Max | Description |
|-----------|---------|-----|-------------|
| `page` | `1` | — | Page number |
| `per_page` | `20` | `100` | Items per page (`30` default for channel messages) |

## Common response `meta`

```json
{
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 20,
    "total": 42
  }
}
```
---

## 1. Community APIs

Applies to both **mentor** and **mentee** unless noted.

### 1.1 Channel members

```
GET /{role}/community/channels/{channelId}/members
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Filter by member **name** |
| `page` | integer | Page number |
| `per_page` | integer | Page size (default `20`) |

**Example**

```
GET /mentor/community/channels/12/members?search=rahul&page=1&per_page=20
```

---

### 1.2 Channels list

```
GET /{role}/community/channels
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Filter by channel **name** |
| `type` | string | `public` or `private` |
| `category` | string | e.g. `general`, `career`, `tech`, `wellness` |
| `joined` | boolean | `1` = channels I joined, `0` = not joined |
| `page` | integer | Page number |
| `per_page` | integer | Page size (default `20`) |

**Example**

```
GET /mentee/community/channels?search=career&type=public&joined=1&page=1
```

---

### 1.3 Channel messages

```
GET /{role}/community/channels/{channelId}/messages
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | integer | Page number |
| `per_page` | integer | Page size (default `30`) |

**Example**

```
GET /mentee/community/channels/12/messages?page=2&per_page=30
```

---

## 2. Mentee curriculum & related APIs

### 2.1 Curriculum (tracks tree)

```
GET /mentee/curriculum
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Track name / slug |
| `track_id` | integer | Specific track |
| `page` | integer | Page number |
| `per_page` | integer | Page size (default `20`) |

---

### 2.2 Curriculum tasks

```
GET /mentee/curriculum/tasks
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Task title |
| `status` | string | `pending`, `in_progress`, `completed` |
| `type` | string | `task`, `reading`, `video`, `project`, `quiz`, `reflection` |
| `week_id` | integer | Filter by week |
| `track_id` | integer | Filter by track |
| `completed_from` | date | Completion date from (`YYYY-MM-DD`) |
| `completed_to` | date | Completion date to (`YYYY-MM-DD`) |
| `page` | integer | Page number |
| `per_page` | integer | Page size (default `20`) |

**Example**

```
GET /mentee/curriculum/tasks?status=completed&completed_from=2026-01-01&completed_to=2026-08-01&page=1&per_page=20
```

---

### 2.3 Curriculum MCQs (topics)

```
GET /mentee/curriculum/mcqs
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Topic name or question text |
| `status` | string | `pending`, `in_progress`, `completed` |
| `week_id` | integer | Filter by week |
| `track_id` | integer | Filter by track |
| `attempted_from` | date | Attempt date from |
| `attempted_to` | date | Attempt date to |
| `page` | integer | Page number |
| `per_page` | integer | Page size (default `20`) |

---

### 2.4 Admin MCQs (quizzes)

```
GET /mentee/curriculum/admin-mcqs
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Quiz title / description |
| `page` | integer | Page number |
| `per_page` | integer | Page size (default `20`) |

---

### 2.5 Mentor videos (mentee view)

```
GET /mentee/mentor-videos
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Collection name |
| `mentor_id` | integer | Filter by mentor |
| `watched` | boolean | `1` = has watched files, `0` = not fully watched |
| `page` | integer | Page number |
| `per_page` | integer | Page size (default `20`) |

---

### 2.6 Plan invoices

```
GET /mentee/invoices
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Invoice number or plan name |
| `status` | string | Invoice status |
| `date_from` | date | Invoice date from |
| `date_to` | date | Invoice date to |
| `page` | integer | Page number |
| `per_page` | integer | Page size (default `20`) |

**Example**

```
GET /mentee/invoices?status=paid&date_from=2026-01-01&date_to=2026-08-26
```

---

### 2.7 Session invoices

```
GET /mentee/session-invoices
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Invoice number |
| `status` | string | Invoice status |
| `date_from` | date | Invoice date from |
| `date_to` | date | Invoice date to |
| `page` | integer | Page number |
| `per_page` | integer | Page size (default `20`) |

---

## 3. Mentor curriculum & related APIs

### 3.1 Tracks

```
GET /mentor/curriculum/tracks
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Track name / slug |
| `mentee_id` | integer | Filter by mentee |
| `is_active` | boolean | `1` / `0` |
| `page` | integer | Page number |
| `per_page` | integer | Page size (default `20`) |

---

### 3.2 Months

```
GET /mentor/curriculum/tracks/{track}/months
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Month title |
| `is_active` | boolean | `1` / `0` |
| `page` | integer | Page number |
| `per_page` | integer | Page size (default `20`) |

---

### 3.3 Weeks

```
GET /mentor/curriculum/months/{month}/weeks
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Week title |
| `is_active` | boolean | `1` / `0` |
| `page` | integer | Page number |
| `per_page` | integer | Page size (default `20`) |

---

### 3.4 Week tasks

```
GET /mentor/curriculum/weeks/{week}/tasks
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Task title |
| `type` | string | Task type |
| `mentee_id` | integer | Filter by mentee |
| `completed` | boolean | `1` / `0` (use with `mentee_id`) |
| `page` | integer | Page number |
| `per_page` | integer | Page size (default `20`) |

---

### 3.5 Week MCQs

```
GET /mentor/curriculum/weeks/{week}/mcqs
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Topic name or question |
| `mentee_id` | integer | Filter by mentee |
| `is_active` | boolean | `1` / `0` |
| `page` | integer | Page number |
| `per_page` | integer | Page size (default `20`) |

---

### 3.6 Supporting materials

```
GET /mentor/curriculum/weeks/{week}/supporting-materials
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Material title |
| `type` | string | Material type |
| `mentee_id` | integer | Filter by mentee |
| `is_active` | boolean | `1` / `0` |
| `page` | integer | Page number |
| `per_page` | integer | Page size (default `20`) |

---

### 3.7 Mentor videos

```
GET /mentor/videos
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Collection name |
| `is_active` | boolean | `1` / `0` |
| `page` | integer | Page number |
| `per_page` | integer | Page size (default `20`) |

---

### 3.8 Mentees list

```
GET /mentor/mentees
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Mentee name or email |
| `page` | integer | Page number |
| `per_page` | integer | Page size (default `20`) |

---

## Quick reference (all endpoints)

| Method | Endpoint |
|--------|----------|
| GET | `/{role}/community/channels` |
| GET | `/{role}/community/channels/{channelId}/members` |
| GET | `/{role}/community/channels/{channelId}/messages` |
| GET | `/mentee/curriculum` |
| GET | `/mentee/curriculum/tasks` |
| GET | `/mentee/curriculum/mcqs` |
| GET | `/mentee/curriculum/admin-mcqs` |
| GET | `/mentee/mentor-videos` |
| GET | `/mentee/invoices` |
| GET | `/mentee/session-invoices` |
| GET | `/mentor/curriculum/tracks` |
| GET | `/mentor/curriculum/tracks/{track}/months` |
| GET | `/mentor/curriculum/months/{month}/weeks` |
| GET | `/mentor/curriculum/weeks/{week}/tasks` |
| GET | `/mentor/curriculum/weeks/{week}/mcqs` |
| GET | `/mentor/curriculum/weeks/{week}/supporting-materials` |
| GET | `/mentor/videos` |
| GET | `/mentor/mentees` |
