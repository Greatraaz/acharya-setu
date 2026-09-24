# Career Services API (Resume & LinkedIn)

Mentee-facing APIs for **Resume development** and **LinkedIn optimisation**. Mentors are not involved — Vedrix admin reviews and uploads deliverables.

## Entitlement rules

| Plan | Resume | LinkedIn |
|------|--------|----------|
| Essential | 1 free every **6 months**, then paid | Paid add-on |
| Growth | 1 free every **6 months**, then paid | Same |
| Premium | 1 free every **3 months**, then paid | Same |
| No active plan | Paid add-on | Paid add-on |

Prices are set in **Admin → App Settings → Career Add-ons** (`addon_resume_price`, `addon_linkedin_price`).

Only one open request per type (`pending_payment` or `submitted`) is allowed at a time.

---

## Auth

All routes require mentee bearer token: `Authorization: Bearer {token}`  
Base path: `/api/v1/mentee/career-services`

---

## 1. Options (prices + entitlement)

`GET /api/v1/mentee/career-services/options`

```json
{
  "status": true,
  "data": {
    "prices": { "resume": 499, "linkedin": 499, "currency": "INR" },
    "resume": {
      "type": "resume",
      "plan_slug": "growth",
      "is_free": true,
      "amount": 0,
      "currency": "INR",
      "entitlement": {
        "included": true,
        "months": 6,
        "used": 0,
        "remaining": 1,
        "window_starts_at": "...",
        "label": "Included in your plan: 1 Resume development every 6 months (available now)."
      }
    },
    "linkedin": { "...same shape..." }
  }
}
```

**UI:** Show `entitlement.label`. If `is_free`, CTA = “Included”; else show `amount` and “Pay & submit”.

---

## 2. List my requests

`GET /api/v1/mentee/career-services`

```json
{
  "status": true,
  "data": [ { "...request object..." } ],
  "meta": { "current_page": 1, "last_page": 1, "total": 2 }
}
```

---

## 3. Show one request

`GET /api/v1/mentee/career-services/{id}`

Returns `data` = request object (below).

---

## 4. Submit request

`POST /api/v1/mentee/career-services`  
`Content-Type: multipart/form-data`

| Field | Resume | LinkedIn |
|-------|--------|----------|
| `type` | `resume` | `linkedin` |
| `resume` | **file** required (pdf/doc/docx, max 10MB) | optional |
| `linkedin_url` | optional | **required** URL |
| `mentee_notes` | optional string | optional |
| `payment_method` | optional `wallet` \| `razorpay` \| `hybrid` (paid only) | same |

Paid add-ons can be paid with **wallet**, **Razorpay**, or **hybrid** (wallet + Razorpay for the shortfall).  
If `payment_method` is omitted on a paid request, the API creates a `pending_payment` request and returns `requires_payment_choice` — then call **Pay** below.

### Free entitlement response (`200`)

```json
{
  "status": true,
  "message": "Request submitted. Our team will review it shortly.",
  "requires_payment": false,
  "requires_payment_choice": false,
  "data": {
    "request": { "...", "status": "submitted", "payment_method": "plan" },
    "payment": null,
    "payment_choice": null
  }
}
```

### Needs payment method (`201`)

```json
{
  "requires_payment": false,
  "requires_payment_choice": true,
  "data": {
    "request": { "id": 12, "status": "pending_payment" },
    "payment": null,
    "payment_choice": {
      "amount": 499,
      "wallet_balance": 200,
      "shortfall": 299,
      "can_pay_full_wallet": false,
      "payment_options": ["wallet", "razorpay", "hybrid"],
      "allow_hybrid": true
    }
  }
}
```

### Paid — wallet (`200`)

Debits wallet immediately → `status: submitted`, `payment_method: wallet`. No Razorpay.

### Paid — Razorpay / hybrid (`201`)

```json
{
  "requires_payment": true,
  "requires_payment_choice": false,
  "data": {
    "request": { "id": 12, "status": "pending_payment", "payment_method": "hybrid", "wallet_amount": 200, "razorpay_amount": 299 },
    "payment": {
      "key": "rzp_...",
      "order_id": "order_...",
      "amount": 299,
      "amount_paise": 29900,
      "wallet_amount": 200,
      "razorpay_amount": 299,
      "total_amount": 499,
      "payment_method": "hybrid",
      "currency": "INR",
      "prefill": { "name": "", "email": "", "contact": "" }
    }
  }
}
```

Open Razorpay with `payment.amount_paise` (online part only). Hybrid wallet debit happens on verify.

---

## 4b. Choose / start payment

`POST /api/v1/mentee/career-services/{id}/pay`

```json
{ "payment_method": "wallet" }
```

Same response shape as store (`requires_payment_choice` / `requires_payment` / `payment`).

---

## 5. Verify payment

`POST /api/v1/mentee/career-services/{id}/verify`

```json
{
  "razorpay_order_id": "...",
  "razorpay_payment_id": "...",
  "razorpay_signature": "..."
}
```

Success → `status: submitted`, mentee waits for admin deliverable. Hybrid: wallet part is debited here.

`GET /options` includes `wallet_balance` and a `payment` object (options / shortfall) for paid quotes.

---

## Request object

| Field | Meaning |
|-------|---------|
| `id` | Request id |
| `type` | `resume` \| `linkedin` |
| `type_label` | Display name |
| `status` | `pending_payment` \| `submitted` \| `completed` \| `cancelled` |
| `status_label` | Human text |
| `linkedin_url` | Mentee URL (linkedin type) |
| `resume_url` | Uploaded resume URL |
| `mentee_notes` | Optional notes |
| `is_paid_addon` | true if charged |
| `amount` / `currency` / `payment_status` | Payment info |
| `payment_method` | `plan` \| `wallet` \| `razorpay` \| `hybrid` |
| `wallet_amount` / `razorpay_amount` | Split amounts |
| `payment_reference` | Wallet or Razorpay ref |
| `admin_notes` | Reviewer suggestions (after complete) |
| `deliverable_url` | Updated resume **or** LinkedIn guide PDF/DOC |
| `completed_at` | When admin finished |
| `invoice` | Invoice object when paid/free (same shape as career-service-invoices API), else `null` |

### Status → UI

| Status | Screen |
|--------|--------|
| `pending_payment` | “Complete payment” / retry |
| `submitted` | “Under review” (no deliverable yet) |
| `completed` | Show `admin_notes` + download `deliverable_url` |

---

## Invoices (transactions)

Career service payments (Razorpay add-ons and plan-included free requests) create a **career service invoice**, same pattern as session/plan invoices.

### Mentee API

| Method | Path |
|--------|------|
| GET | `/api/v1/mentee/career-service-invoices` |
| GET | `/api/v1/mentee/career-service-invoices/{id}` |
| GET | `/api/v1/mentee/career-service-invoices/{id}/download` |

Query filters on list: `search`, `status`, `date_from`, `date_to`, `per_page`.

Invoice object includes `invoice_number`, `payment_method` (`razorpay` \| `plan`), `pricing.total`, `service.type` / `service.label`, Razorpay refs, etc.

Request detail (`GET /career-services/{id}`) also embeds `invoice` when present.


