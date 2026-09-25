# Mock Interviews API

Mentee APIs to request a mock interview for a preferred date/time and duration. Admin confirms and completes the request.

## Entitlement

| Plan | Free mock interview |
|------|---------------------|
| Essential | Paid add-on only |
| Growth | 1 free every **3 months** (max **60 min**) |
| Premium | 1 free every **1 month** (max **60 min**) |
| No plan | Paid |

Paid price = **rate × duration**. Rate is set in **Admin → App Settings → Career Add-ons** → `addon_mock_interview_rate_per_minute` (default ₹15/min).

Durations: `30`, `45`, `60`, `90` minutes. A 90‑minute booking is always paid even if a free entitlement remains (free cap is 60 min).

## Unpaid / `pending_payment` policy

- Unpaid bookings are **never listed** (global scope).
- Choosing a payment method without paying does **not** leave a visible booking.
- Razorpay may create a temporary `pending_payment` row only to attach the order; it is purged on the next submit and hidden everywhere until payment succeeds → `submitted`.

## Auth

`Authorization: Bearer {mentee_token}`  
Base: `/api/v1/mentee/mock-interviews`

---

## 1. Options / quote

`GET /api/v1/mentee/mock-interviews/options?duration=60`

```json
{
  "status": true,
  "data": {
    "rate_per_minute": 15,
    "duration_minutes": 60,
    "amount": 900,
    "is_free": false,
    "currency": "INR",
    "wallet_balance": 250,
    "durations": [30, 45, 60, 90],
    "payment": { "amount": 900, "payment_options": ["wallet", "razorpay", "hybrid"], "…" },
    "entitlement": {
      "included": true,
      "months": 3,
      "used": 0,
      "remaining": 1,
      "max_free_duration": 60,
      "status": "available",
      "next_free_at": null,
      "label": "…"
    }
  }
}
```

---

## 2. List

`GET /api/v1/mentee/mock-interviews` — paginated; excludes unpaid.

## 3. Show

`GET /api/v1/mentee/mock-interviews/{id}` — 404 if unpaid/pending.

## 4. Submit

`POST /api/v1/mentee/mock-interviews`

| Field | Required | Notes |
|-------|----------|-------|
| `duration_minutes` | yes | 30 / 45 / 60 / 90 |
| `preferred_at` | yes | datetime, ≥ now + 2 hours (IST) |
| `timezone` | no | default `Asia/Kolkata` |
| `target_role` | no | |
| `mentee_notes` | no | |
| `payment_method` | no* | `wallet` \| `razorpay` \| `hybrid` when paid |

\* Free bookings need no payment. Paid without method → `requires_payment_choice` and **`request: null`** (no DB row). Resubmit with `payment_method`.

## 5. Pay / verify

`POST .../{id}/pay` `{ "payment_method": "wallet" }`  
`POST .../{id}/verify` Razorpay fields (same as career services).

Success → `status: submitted` (awaiting admin confirmation).

---

## Agora video call

After admin **confirms**, an Agora channel is created (`meeting_channel` / `meeting_link`). The confirming admin hosts the interview (no mentor assignment).

Mentee and admin can join when `meeting.can_join` is true (anytime after confirmation until the preferred slot + duration ends).

### App

`GET /api/v1/mentee/mock-interviews/{id}/agora-token`

Same response shape as session Agora tokens (`app_id`, `channel`, `token`, `uid`, `peer`, `session`). Response `role` is `mentee` or `admin`. Public payloads expose `interviewer` (admin) instead of `mentor`.

`data.meeting` on show/list:

```json
{
  "provider": "agora",
  "channel": "ABCD1234",
  "link": "https://…/as/ABCD1234",
  "can_join": true,
  "window_ends_at": "…"
}
```

### Website

- Mentee / admin: **Join Agora call** on the detail page → `/mock-interviews/{id}/call` (same UI as session calls)

---

## Admin (web)

- List / show under **Mock Interviews**
- **Confirm** — notes optional → `confirmed` (creates Agora channel; admin hosts)
- **Complete** — feedback required → `completed`
- **Cancel** → `cancelled`

## Website (mentee)

- `/mentee/mock-interviews` — list + request
- Create form: datetime, duration, role, notes + wallet/Razorpay/hybrid

## Config bootstrap

`GET /mentee/config` includes:

- `benefits.items` → `mock_interview` (used / remaining / next_free)
- `mock_interviews` — full quote for 60 min
- `app.career_addons.mock_interview_per_minute`
