# Expert app — complete frontend flow

**Product:** Vedrix  
**Audience:** Mobile / app frontend developers  
**Base URL:** `{{baseUrl}} = https://vedrix.online/api/v1`  
**Auth:** `Authorization: Bearer {{userToken}}`  
**Headers:** `Accept: application/json`

There is **no** `/api/v1/expert` prefix. An expert is a session host. After login, if `user.role === "expert"`, call the **same** `/api/v1/mentor/*` APIs that mentors use for onboarding, availability, sessions, notes, and wallet.

Experts do **not** get curriculum, assigned mentees, mentor requests, assessments, videos, or community. Hide those tabs.

Related docs:

- [`mentee-session-booking-flow.md`](./mentee-session-booking-flow.md) — mentee books an expert the same way they book a mentor (`mentor_id` is the expert’s user id)
- [`frontend-coupons-session-earnings-api.md`](./frontend-coupons-session-earnings-api.md) — money fields, 80/20 split, coupons
- [`agora-video-call-frontend.md`](./agora-video-call-frontend.md) — in-app video

---

## 1. What an expert is

| | Mentor | Expert |
|---|---|---|
| `user.role` | `mentor` | `expert` |
| Takes 1:1 paid sessions | Yes | Yes |
| Session notes | Yes | Yes |
| Own charge (`rate_per_minute`) | Yes | Yes |
| Wallet + session earnings | Yes | Yes |
| Curriculum / assigned mentees / community | Yes | **No — hide in UI** |
| Admin list | People → Mentors | People → Experts |
| Listing filter | `?type=mentor` | `?type=expert` |

Sessions are stored with `mentor_id` even when the host is an expert. Always send the expert’s **user id** as `mentor_id`. Do not invent `expert_id`.

---

## 2. App routing (do this first)

After register / login / `GET /auth/me`, branch on `user.role`:

```
role === "mentee"  → mentee app
role === "mentor"  → mentor app (full)
role === "expert"  → expert app (this doc)
```

Inside the expert app:

```
onboarding_completed == false
    → onboarding wizard (steps 1–4 + submit)

onboarding_completed == true AND mentor_status == "pending"
    → “Pending approval” screen (no dashboard)

mentor_status == "rejected" | "suspended"
    → blocked / contact support screen

mentor_status == "approved" AND is_active == true
    → expert home (sessions, notes, availability, wallet, profile)
```

`mentor_status` is reused for experts. Do not look for `expert_status`.

---

## 3. Expert screens (build these)

Bottom nav / tabs:

1. **Home** — upcoming session, charge, wallet unpaid
2. **Sessions** — list + detail + join call + notes
3. **Availability** — weekly slots
4. **Wallet** — balance + session credits
5. **Profile** — photo, bio, rate, logout

Do **not** build for experts:

- Curriculum / tracks / tasks / MCQs
- My mentees
- Mentor requests (accept/reject)
- Assessments
- Mentor videos
- Community channels
- Jobs / quizzes / career tracks

Those mentor APIs may still return 200 for an expert token. The product rule is **hide them**, do not call them.

---

## 4. Register

```
POST /auth/register
```

```json
{
  "name": "Ananya Rao",
  "email": "ananya@example.com",
  "phone": "9876543210",
  "password": "Secret1a",
  "password_confirmation": "Secret1a",
  "role": "expert",
  "accepted_terms": true
}
```

`role` must be `"expert"`. If omitted, the account is created as a mentee.

**Success `201`**

```json
{
  "success": true,
  "status": true,
  "token": "1|xxxxxxxx",
  "user": {
    "id": 137,
    "name": "Ananya Rao",
    "email": "ananya@example.com",
    "role": "expert",
    "onboarding_completed": false,
    "onboarding_step": 0,
    "mentor_status": "pending",
    "rate_per_minute": 0,
    "is_active": true
  }
}
```

Store the Bearer token. Route into the expert onboarding wizard.

OTP helpers (same as other roles):

| Step | Method | Path |
|------|--------|------|
| Send OTP | `POST` | `/auth/send-otp` |
| Verify OTP | `POST` | `/auth/verify-otp` |
| Resend OTP | `POST` | `/auth/resend-otp` |

Body: `{ "identifier", "channel": "email"|"phone", "type": "registration"|"login"|"reset", "otp" }` (otp only on verify).

---

## 5. Login and session

```
POST /auth/login
{ "email": "...", "password": "..." }
```

Returns `{ token, user }` with `user.role`. If `role !== "expert"`, do not open the expert shell.

```
GET  /auth/me
POST /auth/logout
POST /device-tokens     { "token": "<fcm>", "platform": "android"|"ios" }
```

---

## 6. Onboarding (reuse mentor APIs)

All paths below are under `/mentor/onboarding`. Multipart is allowed on step 1 (`avatar` file).

| Step | Method | Path | Required body |
|------|--------|------|----------------|
| Bootstrap | `GET` | `/mentor/onboarding/meta` | — |
| Resume | `GET` | `/mentor/onboarding/status` | — |
| 1 Profile | `POST` | `/mentor/onboarding/step/1` | `name`, `bio` (min ~50 chars HTML/text), optional `gender`, `phone`, `linkedin`, `avatar` |
| 2 Professional | `POST` | `/mentor/onboarding/step/2` | `designation`, `company`, `experience_years`, `rate_per_minute` (≥ 1), `field` (must be a value from `meta.industries`) |
| 3 Skills | `POST` | `/mentor/onboarding/step/3` | `expertise`: string array, min 1 |
| 4 Preferences | `POST` | `/mentor/onboarding/step/4` | optional `preferences.preferred_time`, `preferences.session_length` (`30\|60\|90\|120`), `strengths[]` |
| Submit | `POST` | `/mentor/onboarding/submit` | — |

Copy on expert UI:

- Step 1 title: **About you** (not “Mentor profile”)
- Step 2: **Professional profile** + **Your charge**
- Submit success: **Submitted for review** — admin must approve before the expert is listed

Submit fails `422` if `bio`, `designation`, `expertise`, or `rate_per_minute` are missing. After a successful submit:

- `onboarding_completed = true`
- `mentor_status = "pending"`
- Show the pending screen until `GET /auth/me` returns `mentor_status: "approved"`

Once submitted, step endpoints return `422` (“already submitted”). Edits go through `PATCH /auth/profile`.

---

## 7. Expert home (approved)

Suggested cards:

| Card | Source |
|------|--------|
| Next session | `GET /mentor/sessions?status=upcoming` (or first upcoming item from the list) |
| Charge | `user.rate_per_minute` → show `₹{rate}/min` and `₹{rate×60}/hr` |
| Wallet unpaid | `GET /mentor/wallet/balance` → `balance` |
| Session count | `user.total_sessions` |

---

## 8. Availability

Experts must publish slots or mentees cannot book them. Slot length must be **exactly** `30 | 60 | 90 | 120` minutes.

| Action | Method | Path |
|--------|--------|------|
| List | `GET` | `/mentor/availability` |
| Add slots | `POST` | `/mentor/availability` |
| Replace all | `PUT` | `/mentor/availability` |
| Toggle one | `PATCH` | `/mentor/availability/{id}/toggle` |
| Delete one | `DELETE` | `/mentor/availability/{id}` |

```json
{
  "slots": [
    { "day_of_week": "Monday", "start_time": "10:00", "end_time": "11:00", "is_available": true },
    { "day_of_week": "Monday", "start_time": "11:00", "duration": 60 }
  ]
}
```

`day_of_week` is English full name (`Monday` … `Sunday`). Times are `H:i` in IST.

---

## 9. Sessions, call, notes

### 9.1 List / detail

```
GET /mentor/sessions
```

Query: `status`, `date`, `date_from`, `date_to`, `search`, `per_page`.

There is no separate show endpoint — use the list item for the detail screen. Each item includes `menteeName`, `menteeAvatar`, `scheduledAt`, `duration`, `status`, `notes`, `canJoinCall`, `listAmount`, `mentorEarning`, `platformFee`, and `amountPaid`. The host field is still `mentor_id` (this expert). `mentorEarning` is the expert’s 80% net — show that on the expert receipt, not `amountPaid`.

Durations: `30 | 60 | 90 | 120` only.

### 9.2 Join video

```
GET /mentor/sessions/{id}/agora-token
```

Join in-app Agora with `app_id`, `channel`, `token`, `uid` from the response. See [`agora-video-call-frontend.md`](./agora-video-call-frontend.md). Do not open `meetingLink` in a browser.

### 9.3 Notes

Personal notes (only this expert):

| Method | Path |
|--------|------|
| `GET` | `/mentor/sessions/{id}/notes` |
| `POST` | `/mentor/sessions/{id}/notes` `{ "content": "..." }` |
| `PATCH` | `/mentor/sessions/{id}/notes/{noteId}` |
| `DELETE` | `/mentor/sessions/{id}/notes/{noteId}` |

Shared notes (expert + mentee both see):

| Method | Path |
|--------|------|
| `GET` | `/mentor/sessions/{id}/shared-notes` |
| `POST` / `PUT` | `/mentor/sessions/{id}/shared-notes` |
| `PATCH` | `/mentor/sessions/{id}/shared-notes/{noteId}` |
| `DELETE` | `/mentor/sessions/{id}/shared-notes/{noteId}` |

`POST` personal note upserts the expert’s latest personal note.

---

## 10. Charge and profile

```
PATCH /auth/profile
POST  /auth/upload-photo
DELETE /auth/photo
POST  /auth/change-password
```

Charge:

```json
{ "rate_per_minute": 12 }
```

List price of a session is `rate_per_minute × duration`. Expert net earning is **80% of list price**. Platform keeps 20%. Coupons and plan-free minutes are platform-borne — do not subtract them from expert earnings. Full formula: [`frontend-coupons-session-earnings-api.md`](./frontend-coupons-session-earnings-api.md).

---

## 11. Wallet and earnings

```
GET /mentor/wallet/balance
GET /mentor/wallet/transactions
```

`balance` = unpaid amount still in the expert’s wallet (admin pays this out).

Session credits look like:

- `type`: `credit` or `transfer_in`
- `description`: starts with `Session earning`
- `meta.source`: `session_mentor_payout`
- `amount`: **net** (80% of list) — show this as “You earned”
- `meta.gross_amount`: list price
- `meta.platform_fee`: 20%
- `meta.mentee_paid`: what the mentee actually paid (do **not** show this as expert earnings)

There is **no app withdraw API**. Expert requests / admin payouts happen on web/admin. In the app:

- Show **Wallet unpaid** = `balance`
- Show **Earned** from completed session credit transactions
- Optional: hide wallet **top-up** for experts (mentees top up to pay; experts receive credits)

---

## 12. Notifications

```
GET   /mentor/notifications
PATCH /mentor/notifications/{id}/read
POST  /mentor/notifications/mark-all-read
```

Typical expert events: session booked, session completed / earning credited, payout sent, profile approved.

---

## 13. Mentee side — discover and book experts

Mentee token. Experts appear in the same “mentors” APIs.

| Step | Method | Path |
|------|--------|------|
| List experts only | `GET` | `/mentee/mentors?type=expert` |
| List mentors only | `GET` | `/mentee/mentors?type=mentor` |
| Mixed (default) | `GET` | `/mentee/mentors` |
| Profile | `GET` | `/mentee/mentors/{id}` |
| Slots | `GET` | `/mentee/mentors/{id}/availability?date=YYYY-MM-DD&duration=30` |
| Book | `POST` | `/mentee/sessions` `{ "mentor_id": {id}, ... }` |

Each list item includes `"role": "expert"` or `"mentor"`. Use that for a badge. Booking body still uses **`mentor_id`**.

Do not send a mentee-to-expert “mentor request”. Experts are not assigned long-term mentors; they are booked per session.

Full booking UX (durations, coupons, wallet / Razorpay / hybrid): [`mentee-session-booking-flow.md`](./mentee-session-booking-flow.md).

---

## 14. Suggested expert navigation map

```
Register (role=expert)
    → Onboarding 1–4
        → Submit
            → Pending approval  (poll GET /auth/me)
                → Approved
                    → Home
                    → Sessions → detail → Agora + notes
                    → Availability
                    → Wallet (balance + session credits)
                    → Profile (rate, photo, password)
                    → Logout / delete account
```

Delete account:

```
DELETE /mentor/account
```

---

## 15. Error cheatsheet

| HTTP | When |
|------|------|
| `401` | Missing / expired token |
| `403` | Token is not a session host (`role` not `mentor` or `expert`) |
| `422` | Validation, onboarding locked after submit, slot not 30/60/90/120 |
| `404` | Session / slot not owned by this expert |

---

## 16. QA checklist

- [ ] Register with `role: "expert"` → `user.role` is `expert`, onboarding starts at step 0
- [ ] Register without role does **not** create an expert
- [ ] Expert token can call `/mentor/onboarding/*` and `/mentor/sessions`
- [ ] After submit, UI is pending until admin approves
- [ ] Approved expert is returned by `GET /mentee/mentors?type=expert`
- [ ] Mentee books with `mentor_id` = expert id; session appears on `GET /mentor/sessions`
- [ ] Personal + shared notes work on that session
- [ ] Completed paid session credits expert wallet at 80% of list price
- [ ] Curriculum / mentees / community tabs are hidden
- [ ] Login as mentor still gets the full mentor app
