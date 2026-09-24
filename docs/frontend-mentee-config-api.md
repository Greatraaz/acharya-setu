# Mentee Config API

Single bootstrap endpoint for the mentee app. Call after login (or on app launch).

## Auth

`Authorization: Bearer {mentee_token}`

`GET /api/v1/mentee/config`

---

## Response

```json
{
  "status": true,
  "statuscode": 200,
  "message": "Mentee config fetched successfully.",
  "data": {
    "user": {
      "id": 1,
      "name": "…",
      "email": "…",
      "role": "mentee",
      "subscription_plan": "growth",
      "assigned_mentor_id": 12,
      "wallet_balance": 250,
      "onboarding_completed": true
    },
    "wallet": {
      "balance": 250,
      "currency": "INR",
      "currency_symbol": "₹",
      "total_credited": 1000,
      "total_debited": 750,
      "total_refunded": 0
    },
    "subscription": {
      "has_active": true,
      "subscription_id": "SUB-…",
      "status": "active",
      "billing": "yearly",
      "days_remaining": 240,
      "starts_at": "…",
      "expires_at": "…",
      "plan": {
        "id": 2,
        "name": "Growth",
        "slug": "growth",
        "description": "…",
        "billing": "yearly",
        "price": 29990,
        "price_monthly": 2999,
        "price_yearly": 29990,
        "currency": "INR",
        "duration": 365,
        "discount": null,
        "pricing": { "total": 29990, "…": "…" },
        "benefits": [
          { "label": "Career counselling", "value": "60 min / month" }
        ],
        "limits": {
          "free_session_minutes": 60,
          "free_session_max_duration": 60
        },
        "badge_label": "Most Popular",
        "badge_color": "blue",
        "is_featured": true,
        "color": "#4f46e5"
      }
    },
    "entitlements": {
      "can_access_progress_report": true,
      "period": {
        "label": "monthly_bucket",
        "timezone": "Asia/Kolkata",
        "starts_at": "…",
        "ends_at": "…"
      }
    },
    "benefits": {
      "billing": "yearly",
      "timezone": "Asia/Kolkata",
      "items": [
        {
          "key": "career_counselling",
          "label": "Career counselling",
          "included": true,
          "tracking_enabled": true,
          "unit": "minutes",
          "included_limit": 60,
          "used": 60,
          "remaining": 0,
          "payment_required": true,
          "status": "next_free",
          "next_free_at": "2026-10-24 12:00:00",
          "message": "…"
        },
        {
          "key": "resume",
          "included": true,
          "used": 1,
          "remaining": 0,
          "status": "next_free",
          "next_free_at": "2027-03-24 10:00:00",
          "payment_required": true
        },
        {
          "key": "linkedin",
          "included": true,
          "used": 0,
          "remaining": 1,
          "status": "available",
          "payment_required": false
        },
        {
          "key": "mock_interview",
          "included": true,
          "tracking_enabled": false,
          "status": "available"
        }
      ]
    },
    "career_services": {
      "prices": { "resume": 499, "linkedin": 499, "currency": "INR" },
      "resume": { "is_free": false, "amount": 499, "entitlement": { "…" }, "payment": { "…" } },
      "linkedin": { "…" }
    },
    "mentor": { "id": 12, "name": "…", "email": "…", "avatar_url": "…" },
    "app": {
      "name": "Vedrix",
      "currency": "INR",
      "currency_symbol": "₹",
      "timezone": "Asia/Kolkata",
      "maintenance_mode": false,
      "razorpay": { "enabled": true, "mode": "test", "key": "rzp_test_…" },
      "career_addons": { "resume_price": 499, "linkedin_price": 499 }
    },
    "flags": {
      "onboarding_required": false,
      "has_active_plan": true,
      "has_assigned_mentor": true,
      "can_book_with_wallet": true
    },
    "counts": { "coupons_available": 1 }
  }
}
```

---

## What to use where

| Block | Purpose |
|-------|---------|
| `subscription.plan.benefits` | Marketing list for the **current** billing only (`label` + `value`) |
| `benefits.items` | Dashboard consumption: **used / remaining / next_free_at / payment_required** |
| `career_services` | Full resume/LinkedIn quote + payment options when submitting |

### Removed from config (duplicates / unused)

- `benefit_summary`, `benefits_all`, `benefits_monthly`, `benefits_yearly`
- `features` / `features_monthly` / `features_yearly`
- `pricing_monthly` / `pricing_yearly` (keep single `pricing` for current billing)
- `entitlements.sessions` (use `benefits.items` → `career_counselling` instead)

### `benefits.items[].status`

| Status | UI |
|--------|-----|
| `available` | Show remaining |
| `next_free` | Yearly / multi-bucket — show `next_free_at`; extra = pay |
| `used` | Monthly package — “used your session”; extra = pay |
| `addon` | Always paid |

---

### Notes

- `subscription` is `null` with no active plan.
- Razorpay **secret is never returned**.
- Heavy lists (sessions, curriculum, invoices) stay on their own endpoints.
