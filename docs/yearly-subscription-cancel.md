# Yearly subscription cancellation — frontend guide

Only **yearly** plans can be cancelled. Monthly plans must stay active until they expire.

When a mentee cancels:

1. The **current month is kept** (not refunded).
2. **Unused remaining months** are refunded.
3. Money goes back to the **same place they paid** (Razorpay card/UPI, or wallet).
4. The mentee only **creates a request**. Admin approves it, then the refund and cancel happen.
5. If admin does nothing, the request is **auto-approved after 72 hours**.

Example: yearly plan bought in January.

| Cancel month | Kept (used) | Refunded |
|---|---|---|
| January | 1 month | 11 months |
| June | 6 months | 6 months |
| December | 12 months | ₹0 (plan still cancelled) |

Base URL: `/api/v1`  
Auth: `Authorization: Bearer {mentee_token}` (Sanctum)

---

## What to build in the app

On the mentee **Plans / Current subscription** screen:

1. If `cancellation.can_cancel` is `true`, show **Request cancellation** plus the `quote.summary` text.
2. Confirm, then `POST /mentee/plans/subscription/cancel`.
3. If a request is already pending, hide the button and show “waiting for approval” using `cancellation.request`.
4. If `can_cancel` is `false` (monthly plan), do **not** show a cancel button.
5. After approval, the subscription is gone. Refresh `GET /mentee/plans/subscription/active` or `GET /mentee/config`.

Do **not** immediately treat the plan as cancelled after the POST. Status stays active until admin/auto approval.

---

## 1. Preview (use this before showing the button)

```http
GET /api/v1/mentee/plans/subscription/cancel-preview
```

The same `cancellation` object is also on:

```http
GET /api/v1/mentee/plans/subscription/active
GET /api/v1/mentee/config
```

(`config` → `data.subscription.cancellation`)

### Response

```json
{
  "status": true,
  "message": "Cancellation preview fetched successfully.",
  "data": {
    "can_cancel": true,
    "reason": null,
    "quote": {
      "can_cancel": true,
      "billing": "yearly",
      "subscription_id": "SUB-12345678",
      "plan_name": "Growth",
      "total_months": 12,
      "consumed_months": 6,
      "refund_months": 6,
      "paid_amount": 29990,
      "monthly_amount": 2499.17,
      "consumed_amount": 14995.02,
      "refund_amount": 14994.98,
      "currency": "INR",
      "refund_destination": "razorpay",
      "auto_approve_hours": 72,
      "summary": "6 months already used (₹14,995.02) will not be refunded. ₹14,994.98 for 6 remaining months will be refunded to your original payment method."
    },
    "request": null
  }
}
```

`refund_destination` is `razorpay` (original payment) or `wallet`.

If they already submitted a request:

```json
{
  "can_cancel": false,
  "reason": "A cancellation request is already pending admin approval.",
  "quote": { "...same quote frozen at request time..." },
  "request": {
    "id": 15,
    "status": "pending",
    "refund_amount": 14994.98,
    "consumed_months": 6,
    "refund_months": 6,
    "auto_approve_at": "2026-10-26 14:22:00",
    "hours_until_auto_approve": 70,
    "summary": "..."
  }
}
```

Monthly plan:

```json
{
  "can_cancel": false,
  "reason": "Only yearly subscriptions can be cancelled.",
  "quote": null,
  "request": null
}
```

---

## 2. Submit cancellation request

```http
POST /api/v1/mentee/plans/subscription/cancel
Content-Type: application/json

{
  "subscription_id": "SUB-12345678",
  "reason": "optional note"
}
```

`subscription_id` is the string code from the active subscription payload (example `SUB-12345678`), not the numeric `id`.

**Success (200)**

```json
{
  "status": true,
  "message": "Cancellation request submitted. Admin will review it, or it will auto-approve in 72 hours.",
  "data": {
    "id": 15,
    "status": "pending",
    "refund_amount": 14994.98,
    "auto_approve_at": "2026-10-26 14:22:00",
    "hours_until_auto_approve": 72
  }
}
```

**Errors**

| HTTP | When |
|---|---|
| 404 | Subscription not found for this mentee |
| 422 | Monthly plan, already pending, or not active |

Show `message` from the body.

After this call, keep showing the plan as **active** with a pending-request banner until `status` becomes `approved`.

---

## 3. Request statuses

| `status` | App UI |
|---|---|
| `pending` | Banner: waiting for admin. Show `auto_approve_at` / `hours_until_auto_approve`. Plan still active. |
| `approved` | Plan cancelled. Refund sent. Refresh subscription. |
| `rejected` | Plan still active. They can request again. Show `admin_note` if present. |

Push / in-app notification types:

- `subscription_cancel_requested`
- `subscription_cancel_approved`
- `subscription_cancel_rejected`

Deep-link those to the Plans screen.

---

## 4. Suggested UI copy

**Button:** Request cancellation  

**Helper:** use `quote.summary` as-is.  

**Pending:**  
`Cancellation requested. ₹{refund_amount} will be refunded after approval, or automatically in {hours_until_auto_approve} hours.`

**Monthly:** hide cancel. Optional: “Monthly plans cannot be cancelled.”

---

## 5. Admin (optional for the app)

Web admin already has **Plan Cancellations**. If you build an admin app:

```http
GET  /api/v1/admin/subscription-cancels
POST /api/v1/admin/subscription-cancels/{id}/approve
POST /api/v1/admin/subscription-cancels/{id}/reject
```

Reject body: `{ "admin_note": "optional" }`  
Admin token required.
