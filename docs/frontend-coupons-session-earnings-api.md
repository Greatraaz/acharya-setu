# Frontend Integration Guide — Coupons, Session Pricing & Mentor Earnings

**Audience:** Mobile / frontend developers  
**Auth:** Bearer token (same as existing mentee/mentor APIs)  
**Base path:** `/api/v1`  
**Currency:** INR (no GST on session bookings)

---

## 1. Business rules (read first)

| Party | What they pay / earn |
|--------|----------------------|
| **Mentee** | Pays **list price − coupon discount** |
| **Mentor** | Always earns **80% of full list price** (coupon does **not** reduce mentor earnings) |
| **Platform / Admin** | Takes 20% of list price **and absorbs** the coupon discount |

### Formula

```
list_amount      = mentor.rate_per_minute × duration_minutes
coupon_discount  = min(coupon_value, list_amount)     // flat ₹ off
payable          = list_amount − coupon_discount       // mentee pays this
platform_fee     = list_amount × 0.20
mentor_earning   = list_amount × 0.80
```

### Example

| Item | Amount |
|------|--------|
| List price (₹10/min × 30) | ₹300 |
| Coupon | −₹100 |
| Mentee pays | **₹200** |
| Platform fee (20% of ₹300) | ₹60 |
| Mentor earns | **₹240** |
| Coupon cost borne by | **Admin / platform** |

### Important

- Coupons are **flat ₹**, not percentage.
- Coupons apply only to **paid** bookings (not plan-covered / free sessions).
- Allowed durations: `15 | 30 | 60 | 90` minutes.
- Mentor payout is credited when the session is **completed** and `payment_status = paid`.

---

## 2. Money field glossary

Use these names consistently in UI:

| Field | Meaning | Who cares |
|-------|---------|-----------|
| `listAmount` / `base_amount` / `list_amount` | Full session price before coupon | Mentor earnings base, invoice line |
| `amountPaid` / `amount` / `payable_amount` / `session_amount` | What mentee actually pays | Checkout, Razorpay, wallet debit |
| `couponDiscount` / `coupon_discount` | ₹ off for mentee (platform-funded) | Price breakdown UI |
| `mentorEarning` / `payout.net_amount` | Mentor net wallet credit (80% of list) | Mentor screens |
| `platformFee` / `payout.platform_fee` | 20% of list | Mentor receipt / earnings table |

**Do not** show mentee `amountPaid` as mentor earnings.

---

## 3. Mentee APIs

### 3.1 List my coupons

```
GET /api/v1/mentee/coupons
Authorization: Bearer {token}
```

**Success `200`**

```json
{
  "status": true,
  "statuscode": 200,
  "message": "Available coupons fetched successfully.",
  "data": [
    {
      "id": 3,
      "title": "Welcome ₹100 off",
      "coupon_code": "VED-AB12CD",
      "discount_amount": 100,
      "min_session_amount": 200,
      "starts_at": "2026-09-01",
      "expires_at": "2026-12-31"
    }
  ]
}
```

**UI notes**

- Empty `data: []` → hide coupon picker or show “No coupons available”.
- Only coupons assigned to this mentee and currently valid are returned.

---

### 3.2 Validate / preview coupon

Call this when user selects a coupon (before or while booking).

```
POST /api/v1/mentee/coupons/validate
Authorization: Bearer {token}
Content-Type: application/json

{
  "coupon_code": "VED-AB12CD",
  "mentor_id": 42,
  "duration": 30
}
```

**Success `200`**

```json
{
  "status": true,
  "statuscode": 200,
  "message": "Coupon applied successfully.",
  "data": {
    "valid": true,
    "coupon": {
      "id": 3,
      "title": "Welcome ₹100 off",
      "coupon_code": "VED-AB12CD",
      "discount_amount": 100,
      "min_session_amount": 200,
      "starts_at": "2026-09-01",
      "expires_at": "2026-12-31"
    },
    "base_amount": 300,
    "coupon_discount": 100,
    "payable_amount": 200,
    "currency": "INR",
    "duration": 30,
    "mentor_id": 42
  }
}
```

**Invalid `422`**

```json
{
  "status": false,
  "statuscode": 422,
  "message": "Minimum session booking amount for this coupon is ₹200.",
  "data": {
    "valid": false,
    "base_amount": 150,
    "coupon_discount": 0,
    "payable_amount": 150,
    "currency": "INR"
  }
}
```

**Suggested UI breakdown**

```
Session amount     ₹300
Coupon             −₹100
────────────────────────
You pay            ₹200
```

---

### 3.3 Book session (with optional coupon)

```
POST /api/v1/mentee/sessions
Authorization: Bearer {token}
Content-Type: application/json

{
  "mentor_id": 42,
  "date": "2026-09-20",
  "time": "15:30",
  "duration": 30,
  "title": "Career guidance",
  "agenda": "Discuss interview prep",
  "coupon_code": "VED-AB12CD",
  "payment_method": "wallet"
}
```

| Field | Required | Notes |
|-------|----------|--------|
| `mentor_id` | Yes | Approved mentor |
| `date` | Yes | `Y-m-d`, today or future |
| `time` | Yes | e.g. `"15:30"` (IST) |
| `duration` | Yes | `15 \| 30 \| 60 \| 90` |
| `title` | Yes | Session topic |
| `agenda` | No | Max 1000 chars |
| `coupon_code` | No | Omit or `null` if unused |
| `payment_method` | No* | `wallet \| razorpay \| hybrid`. Omit first to get payment options |

\*Recommended flow: **first call without** `payment_method` → show price + options → **second call with** method.

#### Response A — choose payment method (`200`)

`requires_payment_choice: true`

```json
{
  "status": true,
  "statuscode": 200,
  "message": "Choose a payment method to continue.",
  "data": {
    "requires_payment": false,
    "requires_payment_choice": true,
    "booked": false,
    "amount": 200,
    "base_amount": 300,
    "coupon_discount": 100,
    "currency": "INR",
    "wallet_balance": 50,
    "shortfall": 150,
    "can_pay_full_wallet": false,
    "payment_options": ["wallet", "razorpay", "hybrid"],
    "tax_applicable": false,
    "tax_total": 0,
    "booking_draft": {
      "mentor_id": 42,
      "date": "2026-09-20",
      "time": "15:30",
      "duration": 30,
      "title": "Career guidance",
      "agenda": "Discuss interview prep",
      "coupon_code": "VED-AB12CD"
    }
  }
}
```

**Client rules**

- Charge / show payable = `data.amount` (not `base_amount`).
- Re-send same `coupon_code` on the confirm call.
- If `can_pay_full_wallet` is false, disable full-wallet or offer hybrid/top-up.

#### Response B — Razorpay / hybrid needed (`201`)

`requires_payment: true`

```json
{
  "status": true,
  "statuscode": 201,
  "message": "Complete payment to confirm your booking.",
  "data": {
    "requires_payment": true,
    "requires_payment_choice": false,
    "booked": false,
    "payment_method": "razorpay",
    "order_id": "order_xxxx",
    "amount": 20000,
    "amount_rupees": 200,
    "session_amount": 200,
    "base_amount": 300,
    "coupon_discount": 100,
    "wallet_amount": 0,
    "razorpay_amount": 200,
    "currency": "INR",
    "key": "rzp_xxx",
    "name": "Vedrix",
    "description": "Session with …",
    "prefill": { "name": "…", "email": "…", "contact": "…" },
    "booking_ref": "AS-260920-ABCD"
  }
}
```

**Client rules**

- Razorpay checkout amount = `data.amount` (**paise**).
- For hybrid: wallet part = `wallet_amount`, online = `razorpay_amount`.

#### Response C — booked immediately (`201`)

Wallet / plan / free — `booked: true`

```json
{
  "status": true,
  "statuscode": 201,
  "message": "Session booked! ₹200 deducted from your wallet.",
  "data": {
    "requires_payment": false,
    "requires_payment_choice": false,
    "booked": true,
    "payment_method": "wallet",
    "session": {
      "id": 101,
      "booking_ref": "AS-260920-ABCD",
      "status": "upcoming",
      "payment_status": "paid",
      "payment_method": "wallet",
      "amount": 200,
      "list_amount": 300,
      "coupon_discount": 100,
      "offer_id": 3,
      "wallet_amount": 200,
      "razorpay_amount": 0,
      "currency": "INR",
      "scheduled_at": "2026-09-20 15:30:00",
      "duration_minutes": 30,
      "meeting_link": "…"
    },
    "invoice": { "...": "see session invoices section" }
  }
}
```

#### Coupon errors on book (`422`)

Same messages as validate, e.g. invalid / expired / not assigned / below min amount.

---

### 3.4 Verify Razorpay payment

```
POST /api/v1/mentee/sessions/verify
Authorization: Bearer {token}
Content-Type: application/json

{
  "razorpay_order_id": "order_xxxx",
  "razorpay_payment_id": "pay_xxxx",
  "razorpay_signature": "…"
}
```

**Success** → `booked: true` with `session` (includes `list_amount`, `coupon_discount`) + `invoice`.

Coupon is already locked in the booking draft from the initiate step — do **not** send `coupon_code` again here.

---

### 3.5 List sessions (mentee)

```
GET /api/v1/mentee/sessions
```

Relevant fields per session:

```json
{
  "amountPaid": 200,
  "listAmount": 300,
  "couponDiscount": 100,
  "mentorEarning": 240,
  "platformFee": 60,
  "paymentStatus": "paid",
  "paymentMethod": "wallet",
  "paymentMethodLabel": "Wallet",
  "walletAmount": 200,
  "razorpayAmount": 0,
  "invoice": { "id": 9, "invoice_number": "SIN-202609-00001" }
}
```

**Mentee UI:** show `amountPaid` as “You paid”. Optionally show coupon savings if `couponDiscount > 0`.

---

### 3.6 Session invoices

```
GET  /api/v1/mentee/session-invoices
GET  /api/v1/mentee/session-invoices/{id}
GET  /api/v1/mentee/session-invoices/{id}/download
```

Pricing block in JSON:

```json
"pricing": {
  "base": 200,
  "list_amount": 300,
  "coupon_discount": 100,
  "wallet_amount": 200,
  "razorpay_amount": 0,
  "total": 200,
  "currency": "INR",
  "tax_applicable": false,
  "tax_total": 0
}
```

**Invoice UI**

```
Mentorship session     ₹300
Coupon discount        −₹100
────────────────────────────
Total paid             ₹200
```

- `pricing.total` = amount collected from mentee.
- PDF download already shows list + coupon when present.

---

### 3.7 Welcome wallet credit (new-joinee offer)

On mentee onboarding complete, response may include:

```json
"welcome_wallet_credit": 100
```

or `null` if no offer / already credited.

Show a toast: “₹100 credited to your wallet” when non-null.

---

## 4. Mentor APIs

### 4.1 List sessions

```
GET /api/v1/mentor/sessions
```

Same session object as mentee list.

**Mentor UI (critical)**

| Label | Field |
|-------|--------|
| Session amount | `listAmount` |
| Your earning | `mentorEarning` |
| Platform fee | `platformFee` |
| Coupon (info only) | `couponDiscount` — “Covered by platform” |

Never display `amountPaid` as mentor income.

Example receipt:

```
Session amount     ₹300
Platform fee 20%   −₹60
────────────────────────
You earn           ₹240

(If coupon was used: mentee paid ₹200; discount ₹100 borne by platform)
```

---

### 4.2 Wallet balance & earnings history

```
GET /api/v1/mentor/wallet/balance
GET /api/v1/mentor/wallet/transactions
```

For session payouts, each transaction includes a structured `payout` object when `meta.source === session_mentor_payout`:

```json
{
  "id": 501,
  "type": "credit",
  "amount": 240,
  "description": "Session earning: Career guidance",
  "reference": "SES-EARN-101",
  "status": "completed",
  "meta": { "source": "session_mentor_payout", "...": "..." },
  "payout": {
    "list_amount": 300,
    "gross_amount": 300,
    "mentee_paid": 200,
    "coupon_discount": 100,
    "platform_fee": 60,
    "platform_fee_rate": 0.2,
    "net_amount": 240,
    "session_id": 101,
    "booking_ref": "AS-260920-ABCD",
    "invoice_number": "SIN-202609-00001",
    "mentee_name": "Riya",
    "duration_minutes": 30,
    "session_title": "Career guidance"
  }
}
```

**Earnings table columns**

| Column | Source |
|--------|--------|
| Gross / Session | `payout.list_amount` |
| Fee | `payout.platform_fee` |
| You earned | `payout.net_amount` (same as `amount`) |
| Date / mentee / invoice | `payout.*` |

Fallback if `payout` missing (older txns): use `amount` as net only.

---

## 5. Recommended client flows

### Mentee booking

```
1. Load mentor + slots
2. GET /mentee/coupons
3. User picks duration (+ optional coupon)
4. POST /mentee/coupons/validate  → show base / discount / payable
5. POST /mentee/sessions  (no payment_method) → payment options + amounts
6. User chooses wallet | razorpay | hybrid
7. POST /mentee/sessions  (with payment_method + same coupon_code)
8a. If requires_payment → open Razorpay → POST /mentee/sessions/verify
8b. If booked → success screen + optional invoice
```

### Mentor earnings

```
1. GET /mentor/sessions → show mentorEarning per card
2. GET /mentor/wallet/transactions → earnings history via payout.*
3. On session detail → listAmount, platformFee, mentorEarning
```

---

## 6. Error messages to handle

| Message (examples) | UI action |
|--------------------|-----------|
| Invalid coupon code | Clear selection, show error |
| This coupon is not assigned to your account | Hide / disable coupon |
| This coupon has expired… | Refresh coupon list |
| Minimum session booking amount… | Suggest longer duration / other mentor |
| Insufficient wallet balance | Offer top-up / hybrid / razorpay |
| That time slot has already passed | Refresh slots |

Always prefer server `message` string for user-facing text.

---

## 7. Checklist for frontend

### Mentee app
- [ ] Coupon list + validate before pay
- [ ] Pass `coupon_code` on book
- [ ] Show `base_amount` − `coupon_discount` = payable
- [ ] Charge Razorpay/wallet with **payable**, not list
- [ ] Invoice shows list + coupon + total paid
- [ ] Handle `welcome_wallet_credit` after onboarding

### Mentor app
- [ ] Use `mentorEarning` / `payout.net_amount` everywhere for income
- [ ] Use `listAmount` as session gross
- [ ] Do **not** treat `amountPaid` as mentor income
- [ ] Optional: show coupon as “platform covered” if `couponDiscount > 0`

---

## 8. Quick reference — endpoints

| Method | Endpoint | Role |
|--------|----------|------|
| GET | `/api/v1/mentee/coupons` | List coupons |
| POST | `/api/v1/mentee/coupons/validate` | Preview discount |
| POST | `/api/v1/mentee/sessions` | Book (+ `coupon_code`) |
| POST | `/api/v1/mentee/sessions/verify` | Confirm Razorpay |
| GET | `/api/v1/mentee/sessions` | Sessions + money fields |
| GET | `/api/v1/mentee/session-invoices` | Invoices |
| GET | `/api/v1/mentor/sessions` | Sessions + `mentorEarning` |
| GET | `/api/v1/mentor/wallet/transactions` | Earnings + `payout` |

---

## 9. Contact / QA tip

Test with a mentee who has an assigned coupon in Admin → Offers, book a paid mentor session, complete it, then verify:

1. Mentee charged = list − coupon  
2. Mentor wallet credit = 80% of list  
3. Session list / invoice / wallet txn all show matching numbers  

Questions on field mapping: sync with backend on this doc version.
