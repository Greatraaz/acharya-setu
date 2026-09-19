## What changed (read this first)

| Old behaviour | New behaviour |
|---------------|---------------|
| Durations `15, 30, 60, 90` | Only **`30, 60, 90, 120`** |
| Slot UI showed start time only (`9:00 AM`) | Show **full range** (`9:00 AM – 10:00 AM`) |
| Could book shorter session inside a longer window | Duration must **exactly match** mentor window |
| Plan free = session **count** | Plan free = **minutes** (career counselling) |
| Plan/free sessions → mentor unpaid | Mentor still earns **80% of list**; admin pays the rest |
| GST sometimes assumed | **No GST** on session bookings |

---

## Screen flow (implement this UX)

```
1. Pick mentor
2. Load plan free minutes (optional banner)
3. Pick date (calendar)
4. Pick duration chip: 30 | 60 | 90 | 120
5. Load slots for date + duration
6. Show slots as ranges (start – end), not start-only
7. User taps a slot → duration = that slot’s duration
8. Optional: apply coupon (only if NOT plan-covered)
9. Book
   ├─ plan free / fully free → success (no payment UI)
   ├─ needs payment choice → show Wallet / Razorpay / Hybrid
   └─ Razorpay / Hybrid → open Razorpay → verify
10. Show confirmation + invoice link
```

---

## Money fields (use these names in UI)

| Field | Meaning | Show to mentee? |
|-------|---------|-----------------|
| `list_amount` / `base_amount` | Full price = `rate × duration` | Yes (strikethrough if discount) |
| `coupon_discount` | Flat ₹ coupon off | Yes, if > 0 |
| `amount` / `payable_amount` / `session_amount` | What mentee pays | Yes — **this is the charge** |
| `platform_subsidy` | Admin cost (plan free or coupon) | No (internal) |
| `tax_total` | Always `0` | Do not show GST lines |
| `tax_applicable` | Always `false` | Do not show GST |

**Never** show mentee `amount` as mentor earnings.

**Formula (no GST):**

```
list_amount     = mentor.rate_per_minute × duration
coupon_discount = flat ₹ (0 if none / plan-covered)
payable         = list_amount − coupon_discount
```

---

## APIs cheat sheet

| Step | Method | Endpoint |
|------|--------|----------|
| Free minutes left | `GET` | `/api/v1/mentee/plans/subscription/consumption` |
| Slots for a day | `GET` | `/api/v1/mentee/mentors/{id}/availability?date=YYYY-MM-DD&duration=30` |
| My coupons | `GET` | `/api/v1/mentee/coupons` |
| Preview coupon | `POST` | `/api/v1/mentee/coupons/validate` |
| Book (step 1) | `POST` | `/api/v1/mentee/sessions` |
| Confirm Razorpay | `POST` | `/api/v1/mentee/sessions/verify` |
| Wallet balance | `GET` | `/api/v1/mentee/wallet/balance` |
| Session invoices | `GET` | `/api/v1/mentee/session-invoices` |

All require `Authorization: Bearer {token}`.

---

## 1. Plan free minutes banner

### Request

```http
GET /api/v1/mentee/plans/subscription/consumption
```

### Success (has plan)

```json
{
  "status": true,
  "message": "Plan consumption fetched successfully.",
  "data": {
    "has_active_subscription": true,
    "sessions": {
      "unit": "minutes",
      "included_limit": 30,
      "used": 0,
      "remaining": 30,
      "max_session_minutes": 30,
      "benefit": "career_counselling",
      "next_booking_free": true,
      "unlimited": false,
      "percent_used": 0,
      "resets_at": "2026-10-03 18:00:00"
    },
    "period": {
      "starts_at": "2026-09-03 18:00:00",
      "ends_at": "2026-10-03 18:00:00",
      "timezone": "Asia/Kolkata"
    }
  }
}
```

### UI rules

| Condition | UI |
|-----------|-----|
| `has_active_subscription: false` | Hide free-minutes banner |
| `remaining >= duration` AND `duration <= max_session_minutes` | Show: “This booking is covered by your plan (free)” |
| Essential: remaining 30, user picks 60 | **Not free** — show normal payment |
| `remaining: 0` | Show: “Free minutes used — paid booking” |

**Essential plan:** 30 free minutes, max booking length **30** → only a 30-min session can be free, once per billing period.

**Growth / Premium:** 60 free minutes, max **60**.

---

## 2. Load slots (IMPORTANT)

### Request

```http
GET /api/v1/mentee/mentors/{mentorId}/availability?date=2026-09-20&duration=60
```

| Query | Required | Notes |
|-------|----------|-------|
| `date` | Yes | `YYYY-MM-DD` |
| `duration` | Recommended | `30` \| `60` \| `90` \| `120` — filters to exact window length |

### Response (use `slot_options`)

```json
{
  "mentor_id": 12,
  "date": "2026-09-20",
  "day": "saturday",
  "available": true,
  "has_schedule": true,
  "slots": ["09:00", "11:00"],
  "slot_options": [
    {
      "start_time": "09:00",
      "end_time": "10:00",
      "duration": 60,
      "label": "09:00–10:00"
    },
    {
      "start_time": "11:00",
      "end_time": "11:30",
      "duration": 30,
      "label": "11:00–11:30"
    }
  ]
}
```

> When `duration=60` is passed, only 60-minute windows are returned.  
> When `duration` is omitted, all open windows for that day are returned.

### Frontend display rules

1. **Render from `slot_options`**, not from `slots` alone.
2. Label each chip/button as:

   ```
   {format(start_time)} – {format(end_time)}
   ```

   Example: `9:00 AM – 10:00 AM`

3. On tap:
   - `selectedTime = start_time` (send this as `time` when booking)
   - `selectedDuration = duration` (must equal window length)
4. Do **not** allow booking 30 minutes inside a 60-minute window.
5. Empty `slot_options` → “No slots for this duration. Try another length or date.”

### Duration chips

Only these four:

```
[ 30m ]  [ 60m ]  [ 90m ]  [ 120m ]
```

Remove `15m` everywhere.

When user changes duration chip → **reload** availability with the new `duration`.

---

## 3. Coupons (paid bookings only)

Skip coupons when the session will be plan-covered.

### List

```http
GET /api/v1/mentee/coupons
```

### Preview / validate

```http
POST /api/v1/mentee/coupons/validate
Content-Type: application/json

{
  "coupon_code": "VED-AB12CD",
  "mentor_id": 12,
  "duration": 30
}
```

### Success

```json
{
  "status": true,
  "data": {
    "valid": true,
    "base_amount": 300,
    "coupon_discount": 100,
    "payable_amount": 200,
    "currency": "INR"
  }
}
```

Show price breakdown:

```
List        ₹300
Coupon     −₹100
You pay     ₹200
(No GST)
```

---

## 4. Book session

### Request

```http
POST /api/v1/mentee/sessions
Content-Type: application/json

{
  "mentor_id": 12,
  "date": "2026-09-20",
  "time": "09:00",
  "duration": 60,
  "title": "Career counselling",
  "agenda": "Discuss roadmap",
  "coupon_code": "VED-AB12CD",
  "payment_method": null
}
```

| Field | Required | Notes |
|-------|----------|-------|
| `mentor_id` | Yes | Approved mentor |
| `date` | Yes | `YYYY-MM-DD`, today or future |
| `time` | Yes | `HH:mm` = `slot_options[].start_time` |
| `duration` | Yes | Must equal that slot’s `duration` |
| `title` | Yes | Max 255 |
| `agenda` | No | Max 1000 |
| `coupon_code` | No | Ignored if plan-covered |
| `payment_method` | No | Omit first → get payment options. Then send `wallet` \| `razorpay` \| `hybrid` |

### Response branching (handle all 4)

```
book()
  │
  ├─ booked: true, payment_method: plan|free
  │     → Success screen (₹0). No Razorpay.
  │
  ├─ requires_payment_choice: true
  │     → Show payment sheet (wallet / razorpay / hybrid)
  │     → Call book again WITH payment_method
  │
  ├─ requires_payment: true
  │     → Open Razorpay Checkout with data.key + data.order_id
  │     → On success → POST /sessions/verify
  │
  └─ booked: true, payment_method: wallet
        → Success screen (wallet debited)
```

---

### A) Plan free / free session — immediate book

```json
{
  "status": true,
  "statuscode": 201,
  "message": "Session booked using your Essential plan free minutes (0 free minutes left this period).",
  "data": {
    "booked": true,
    "requires_payment": false,
    "requires_payment_choice": false,
    "payment_method": "plan",
    "session": {
      "id": 101,
      "booking_ref": "AS-12345678",
      "amount": 0,
      "list_amount": 300,
      "platform_subsidy": 300,
      "coupon_discount": 0,
      "payment_status": "waived",
      "payment_method": "plan",
      "duration_minutes": 30,
      "scheduled_at": "2026-09-20 09:00:00",
      "tax_applicable": false,
      "tax_total": 0
    },
    "plan_allowance": {
      "covered": false,
      "minutes_remaining": 0,
      "minutes_limit": 30,
      "max_session_minutes": 30
    },
    "pricing": {
      "list_amount": 300,
      "mentee_paid": 0,
      "coupon_discount": 0,
      "platform_subsidy": 300
    }
  }
}
```

**UI:** “Booked with plan · Free” / “No payment required”.

---

### B) Need payment method

```json
{
  "status": true,
  "statuscode": 200,
  "message": "Choose a payment method to continue.",
  "data": {
    "booked": false,
    "requires_payment_choice": true,
    "requires_payment": false,
    "amount": 200,
    "list_amount": 300,
    "base_amount": 300,
    "coupon_discount": 100,
    "platform_subsidy": 100,
    "wallet_balance": 50,
    "shortfall": 150,
    "can_pay_full_wallet": false,
    "payment_options": ["wallet", "razorpay", "hybrid"],
    "tax_applicable": false,
    "tax_total": 0,
    "booking_draft": {
      "mentor_id": 12,
      "date": "2026-09-20",
      "time": "09:00",
      "duration": 30,
      "title": "Career counselling",
      "coupon_code": "VED-AB12CD"
    }
  }
}
```

**Payment sheet rules**

| Option | Show when | Action |
|--------|-----------|--------|
| Wallet | `"wallet"` in `payment_options` and `can_pay_full_wallet` | Re-POST book with `"payment_method": "wallet"` |
| Razorpay | always if listed | Re-POST with `"payment_method": "razorpay"` |
| Hybrid (Wallet + Razorpay) | `"hybrid"` in options (`0 < wallet < amount`) | Re-POST with `"payment_method": "hybrid"` |

Price on sheet:

```
List          ₹{list_amount}
Coupon       −₹{coupon_discount}     // hide if 0
Pay now       ₹{amount}
No GST
Wallet        ₹{wallet_balance}       // if hybrid: “₹X from wallet + ₹Y online”
```

If wallet insufficient and user taps Wallet → show top-up CTA (`needs_topup` / `topup_url` may be in error).

---

### C) Razorpay / Hybrid — open checkout

```json
{
  "status": true,
  "statuscode": 201,
  "message": "Complete payment to confirm your booking.",
  "data": {
    "booked": false,
    "requires_payment": true,
    "payment_method": "hybrid",
    "order_id": "order_xxx",
    "key": "rzp_test_xxx",
    "amount": 15000,
    "amount_rupees": 150,
    "session_amount": 200,
    "list_amount": 300,
    "coupon_discount": 100,
    "wallet_amount": 50,
    "razorpay_amount": 150,
    "currency": "INR",
    "name": "Vedrix",
    "description": "Session with Rahul",
    "prefill": { "name": "...", "email": "...", "contact": "..." },
    "tax_applicable": false,
    "tax_total": 0
  }
}
```

**Razorpay Checkout.js**

```js
const options = {
  key: data.key,
  amount: data.amount,          // paise
  currency: data.currency,
  name: data.name,
  description: data.description,
  order_id: data.order_id,
  prefill: data.prefill,
  handler: async (response) => {
    // POST /api/v1/mentee/sessions/verify
  },
};
new Razorpay(options).open();
```

**Important:** Charge Razorpay only `data.amount` / `data.razorpay_amount` (remainder).  
For hybrid, wallet is debited **after** verify — show that in copy.

---

### D) Verify payment

```http
POST /api/v1/mentee/sessions/verify
Content-Type: application/json

{
  "razorpay_order_id": "order_xxx",
  "razorpay_payment_id": "pay_xxx",
  "razorpay_signature": "..."
}
```

### Success

```json
{
  "status": true,
  "message": "Payment successful! Your session is booked.",
  "data": {
    "booked": true,
    "session": { "id": 102, "booking_ref": "AS-...", "amount": 200, "list_amount": 300 },
    "invoice": { "id": 55, "invoice_number": "SIN-202609-00001" }
  }
}
```

→ Navigate to success / session detail. Offer invoice download from `session-invoices`.

---

## 5. Error handling (common)

| HTTP | When | UI |
|------|------|-----|
| 409 | Slot held / just taken by another mentee (`slot_busy: true`) | Toast `message`, reload slots, do **not** retry payment on same slot |
| 409 | Paid but slot lost (`payment_refunded: true`) | “Slot taken — payment refunded. Pick another time.” |
| 422 | Slot passed / wrong duration / overlap | Show `message`, reload slots |
| 422 | Duration not exact match | “Pick a slot that matches this duration” |
| 422 | Insufficient wallet | Show top-up + offer hybrid/razorpay |
| 422 | Coupon invalid | Clear coupon, show `message` |
| 404 | Mentor not found | Back to mentor list |

Always prefer API `message` for toast text.

### Concurrent booking (two mentees, same slot)

Backend guarantees **only one** mentee gets the slot:

1. **Checkout hold (10 min)** — first mentee to start Razorpay/hybrid claims the slot; second gets `409 slot_busy` **before** paying.
2. **MySQL slot lock** — wallet / plan / free / verify create under `GET_LOCK` so two inserts cannot both succeed.
3. **If the second somehow paid** (rare race) — verify returns `409` and Razorpay is **auto-refunded** (`payment_refunded: true`). Session is **not** created.

```json
{
  "status": false,
  "statuscode": 409,
  "message": "This time slot was booked by someone else. Your payment has been refunded automatically. Please choose another slot.",
  "slot_busy": true,
  "payment_refunded": true,
  "booked": false
}
```

**Frontend must:**

- On `409` + `slot_busy` → stop checkout, refresh availability, ask user to pick another slot.
- Never treat `booked: false` as success even if Razorpay `handler` fired.
- Only navigate to success when verify (or book) returns `booked: true`.

---

## 6. Suggested UI states

### Price block

```
₹200          ← payable (big)
₹300          ← list, strikethrough if discount/credit
− ₹100 coupon
No GST
```

### Slot chip

```
┌─────────────────────┐
│  9:00 AM – 10:00 AM │   ← selected
│       60 min        │
└─────────────────────┘
```

### Plan banner

```
Your Essential plan: 30 free min left
Applies only to 30-minute sessions
```

### Payment methods

```
○ Wallet          (₹500 available)
○ Pay online      (Razorpay)
○ Wallet + Online (₹50 wallet + ₹150 online)
```

---
## 7. Mentor app note (availability)

Mentors must add windows of **exactly** 30, 60, 90, or 120 minutes.

| Mentor saves | Mentee sees |
|--------------|-------------|
| 09:00–10:00 | `9:00 AM – 10:00 AM` (60m) |
| 11:00–11:30 | `11:00 AM – 11:30 AM` (30m) |

Invalid lengths (e.g. 45 min) are rejected by the API.

---
