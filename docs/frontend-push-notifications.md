# Push notifications — frontend guide

Mentors and mentees now receive in-app notifications. If the app registers an FCM device token, the same event is also pushed to the device.

The Laravel web app already shows a bell in the navbar. This doc is for the **mobile / SPA frontend**.

Base URL: `/api/v1`  
Auth: `Authorization: Bearer {token}` (Sanctum)

Use the mentee token with mentee routes, mentor token with mentor routes. Device-token routes work for both.

---

## What you need to build

1. Notification list + unread badge
2. Mark one / all as read
3. Register the FCM token after login; unregister on logout
4. Handle a tap on a push (deep link from `type` + `data`)
5. Poll the list while the app is open (every 12–20 seconds is enough)

There is no websocket. Real-time on device = FCM. Real-time in an open screen = poll.

---

## 1. List notifications

Mentee:

```http
GET /api/v1/mentee/notifications
```

Mentor:

```http
GET /api/v1/mentor/notifications
```

Returns the latest **50** items plus the **total** unread count (unread can be higher than 50).

```json
{
  "notifications": [
    {
      "id": 42,
      "type": "session_booked",
      "title": "Session booked",
      "body": "Your session with Priya is scheduled for 23 Oct 2026, 4:30 PM.",
      "data": {
        "session_id": 18,
        "event": "booked"
      },
      "url": "https://vedrix.online/mentee/sessions/18",
      "is_read": false,
      "created_at": "2026-10-22T16:45:01+05:30",
      "created_at_human": "2 minutes ago"
    }
  ],
  "unreadCount": 3,
  "unread_count": 3
}
```

| Field | Notes |
|---|---|
| `id` | Integer. Use this to mark read. |
| `type` | Stable key for icons and routing. Prefer this over parsing `title`. |
| `data` | Object, or `{}` when empty. IDs are numbers in this JSON. |
| `url` | Laravel **web** URL. Use it on web. On mobile, route with `type` + `data` instead. |
| `unreadCount` / `unread_count` | Same value. Either is fine. |

---

## 2. Mark read

One item (mentee / mentor):

```http
PATCH /api/v1/mentee/notifications/{id}/read
PATCH /api/v1/mentor/notifications/{id}/read
```

```json
{ "message": "Marked read" }
```

Unknown ids still return 200 (no error). Only the signed-in user’s rows can be updated.

All unread:

```http
POST /api/v1/mentee/notifications/mark-all-read
POST /api/v1/mentor/notifications/mark-all-read
```

```json
{ "message": "All marked read" }
```

Suggested UX: tap a row → mark that id read → navigate. “Mark all” on the list header.

---

## 3. Register the device for FCM

Call **after login** (and again if the FCM token refreshes). Same route for mentor and mentee.

```http
POST /api/v1/device-tokens
Content-Type: application/json

{
  "token": "<FCM registration token>",
  "platform": "android"
}
```

| Field | Required | Values |
|---|---|---|
| `token` | yes | FCM token, 20–512 chars |
| `platform` | no | `android`, `ios`, or `web`. Default `web`. |

Success:

```json
{
  "status": true,
  "statuscode": 200,
  "message": "Device registered for push notifications.",
  "device": { "id": 1, "platform": "android" }
}
```

The token is unique. Re-registering moves it to the current user (covers logout/login on the same phone).

On logout:

```http
DELETE /api/v1/device-tokens
Content-Type: application/json

{ "token": "<same FCM token>" }
```

```json
{
  "status": true,
  "statuscode": 200,
  "message": "Device unregistered."
}
```

If no FCM server key is saved in Admin → Settings → Notifications, the in-app list still works and FCM is skipped. Register the token anyway so push starts working when ops add the key.

---

## 4. Incoming FCM payload

When a key is configured, the server sends:

```json
{
  "notification": {
    "title": "Session booked",
    "body": "Your session with Priya is scheduled for 23 Oct 2026, 4:30 PM.",
    "sound": "default"
  },
  "data": {
    "id": "42",
    "type": "session_booked",
    "url": "https://vedrix.online/mentee/sessions/18",
    "title": "Session booked",
    "body": "Your session with Priya is scheduled for 23 Oct 2026, 4:30 PM.",
    "click_action": "https://vedrix.online/mentee/sessions/18"
  }
}
```

FCM `data` values are **strings**. Parse `id` with `Number(data.id)` before calling mark-read.

On notification tap:

1. `PATCH .../notifications/{id}/read`
2. Open the in-app screen from `type` (table below)
3. Refresh the list / badge

---

## 5. Deep links (`type` + `data`)

`url` is a website path. Map these types in the app:

| `type` | Who gets it | `data` | Open this screen |
|---|---|---|---|
| `session_booked` | mentor + mentee | `session_id`, `event` | Session detail |
| `session_cancelled` | mentor + mentee | `session_id`, `event` | Session detail |
| `session_completed` | mentor + mentee | `session_id`, `event` | Session detail |
| `session_reminder` | mentor + mentee | `session_id`, `event` | Session detail (or join-call if you have it) |
| `mentor_request` | mentor | `mentor_request_id`, `mentee_id` | Mentor requests |
| `mentor_request_accepted` | mentee | `mentor_request_id`, `mentor_id` | My mentor |
| `mentor_request_rejected` | mentee | `mentor_request_id`, `mentor_id` | My mentor / find mentors |
| `mentor_assigned` | mentee | `mentor_id` | My mentor |
| `mentor_approved` | mentor | — | Mentor dashboard |
| `mentor_rejected` | mentor | `reason` | Pending / onboarding |
| `task_assigned` | mentee | `task_id` **or** `curriculum_task_id` | If `task_id` → mentee task detail. If `curriculum_task_id` → journey/curriculum. |
| `assessment_assigned` | mentee | `assessment_id` | Assessments list (or that assessment) |
| `community_reply` | mentor or mentee | `message_id`, `channel_id`, `parent_id` | Channel thread |
| `community_invite` | mentor or mentee | `channel_id`, `invitation_id` (API invites) | Channel, or invitations list |
| `wallet_topup` | mentee (and API wallet users) | `amount`, `reference` | Wallet |
| `subscription` | mentee | `plan_slug` | Plans |
| `broadcast` | mentor and/or mentee | — | Notification detail / home. Admin may send a custom `type`; still show `title` + `body`. |

Unknown `type`: show `title` + `body` and do not crash.

---

## 6. Suggested client flow

```
login
  → request FCM permission
  → POST /device-tokens
  → GET /{role}/notifications   // seed list + badge

app in foreground
  → poll GET every 15s (or on resume)
  → if unread_count increased, refresh badge

FCM received in background
  → OS shows title/body
  → on tap: mark read + deep link

logout
  → DELETE /device-tokens
```

Badge = `unread_count` from the list endpoint. Do not count only the 50 returned rows.

---

## 7. Errors

| Status | When |
|---|---|
| 401 | Missing / expired token |
| 403 | Wrong role (e.g. mentee hitting mentor URL) |
| 422 | Device token missing or `platform` not `web`/`android`/`ios` |

Mark-read never 404s for a bad id.

---

## 8. Web-only (ignore on mobile)

These session-cookie routes power the Laravel navbar bell. Do not use them from the app.

- `GET /notifications/feed`
- `POST /notifications/{id}/read`
- `POST /notifications/read-all`
