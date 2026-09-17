# Plan listing, discount, and subscribe APIs

Plan discounts are **percentage off the plan list price**, configured in Admin → Plans. They apply automatically at checkout if the purchase is made **on or before** `discount.expires_at` (Asia/Kolkata calendar date). GST is calculated **after** the discount.

No coupon code is required.

## Formula

```
original_base     = plan.price_monthly
discount_amount   = original_base × discount_percent / 100     // only if discount is active
base              = original_base − discount_amount            // taxable value
tax               = base × (CGST% + SGST% + IGST%) / 100
plan_total        = base + tax                                 // new plan price before leftover-day credit
payable           = max(0, plan_total − unused_day_credit)     // send this to Razorpay
```

If `discount_percent` is 0, or today’s date is after `discount_expires_at`, `discount.is_active` is `false` and the full list price is used as the plan total.

---

## Upgrade credit (prorated leftover days)

Switching from an active paid plan to a different plan **credits unused days** of the current plan at that plan’s daily rate, then charges the difference. The new plan’s billing period and benefits start **now** (session usage resets).

Example: Plan A ₹30 / 30 days. After 25 days, 5 days remain → credit ₹5. Plan B ₹60 → **pay ₹55**.

```
daily_rate      = amount_paid_on_current_plan / total_days
remaining_days  = calendar days left until current expires_at (Asia/Kolkata)
credit          = round(daily_rate × remaining_days, 2)
payable         = max(0, new_plan.pricing.total − credit)
```

- Credit uses what the mentee **actually paid** for the current plan (tax and plan discount already included).
- If credit ≥ new plan total, `payable` is `0` and the upgrade activates immediately (no Razorpay).
- No cash refund when credit is larger than the new plan.
- Same-plan repurchase while active is still blocked.

When the request is authenticated, each plan object includes `checkout`. **Charge `checkout.payable`**, not `pricing.total`, when `checkout.is_upgrade` is true.

### `checkout` object

| Field | Meaning |
|-------|---------|
| `is_upgrade` | Current paid plan is different from this one |
| `plan_total` | Same as `pricing.total` (new plan with GST / discount) |
| `payable` | Amount to send to Razorpay. `0` = activate without payment |
| `currency` | e.g. `INR` |
| `credit.amount` | Unused-day credit in rupees |
| `credit.remaining_days` | Days left on the current plan |
| `credit.used_days` | Days already used |
| `credit.total_days` | Length of the current billing period |
| `credit.daily_rate` | `amount_paid / total_days` |
| `credit.from_plan_id` / `from_plan_name` | Plan being replaced |

Subscribe and verify also return `data.checkout` (or `data.subscription.checkout` on verify). Use `data.amount` / `data.amount_paise` from subscribe — those already equal `payable`.

Invoice `pricing.credit` is a snapshot of that leftover-day credit. `pricing.total` on the invoice is the amount actually paid.

---

## 1. List plans

`GET /api/v1/plans`

Public. Returns active plans. Each item is the same shape as **Show plan**. Send a bearer token so `checkout` includes leftover-day credit for the logged-in mentee.

```json
{
  "status": true,
  "message": "Plans fetched successfully.",
  "data": [ { "...plan object..." } ]
}
```

## 2. Show plan

`GET /api/v1/plans/{id}`

Public. Same `checkout` note as list.

### Discount object (`discount`)

`null` when no percentage is configured. Otherwise:

| Field | Type | Meaning |
|-------|------|---------|
| `percent` | number | Off the list price, e.g. `10` |
| `expires_at` | `YYYY-MM-DD` or `null` | Last day the offer is valid. `null` = no end date |
| `is_active` | bool | Use this to show the sale UI |
| `label` | string | Ready-made copy, e.g. `10% off until 30 Sep 2026` |

### Pricing object (`pricing`)

| Field | Meaning |
|-------|---------|
| `original_base` | List price before discount |
| `discount_percent` | `0` if offer is not active |
| `discount_amount` | ₹ off the list price |
| `discount_active` | Same as `discount.is_active` for this checkout |
| `discount_expires_at` | Configured last day (even if currently inactive) |
| `original_total` | What the mentee would pay with tax and **no** discount (for strikethrough) |
| `base` | Taxable value after discount |
| `taxes` | **Use this for UI.** Only taxes filled in admin, e.g. `[{ "code": "IGST", "percent": 18, "amount": 485.84 }]`. Empty if no GST is set. |
| `cgst_percent` / `sgst_percent` / `igst_percent` | `null` if that field was left blank in admin. Do **not** render a line when null or 0. |
| `tax_total` | Sum of `taxes[].amount` |
| `total` | New plan price after discount + GST. **Not** the Razorpay amount on upgrade — use `checkout.payable` |
| `currency` | e.g. `INR` |
| `billing` | `monthly` |

If admin fills only IGST, `taxes` has IGST only — do not show CGST or SGST. If all three are filled, all three are applied and listed.

`price` / `price_monthly` remain the **list** monthly price (not discounted). Do not charge those when `pricing.discount_active` is true.

### Example (10% off Growth, valid through 30 Sep)

```json
{
  "status": true,
  "data": {
    "id": 4,
    "name": "Growth",
    "price_monthly": 2999,
    "discount": {
      "percent": 10,
      "expires_at": "2026-09-30",
      "is_active": true,
      "label": "10% off until 30 Sep 2026"
    },
    "pricing": {
      "original_base": 2999,
      "discount_percent": 10,
      "discount_amount": 299.9,
      "discount_active": true,
      "discount_expires_at": "2026-09-30",
      "original_total": 3538.82,
      "base": 2699.1,
      "cgst_percent": null,
      "sgst_percent": null,
      "igst_percent": 18,
      "taxes": [
        { "code": "IGST", "percent": 18, "amount": 485.84 }
      ],
      "tax_total": 485.84,
      "total": 3184.94,
      "currency": "INR",
      "billing": "monthly"
    },
    "checkout": {
      "is_upgrade": true,
      "plan_total": 3184.94,
      "payable": 3179.94,
      "currency": "INR",
      "credit": {
        "is_upgrade": true,
        "amount": 5,
        "remaining_days": 5,
        "used_days": 25,
        "total_days": 30,
        "daily_rate": 1,
        "from_plan_id": 3,
        "from_plan_name": "Essential"
      }
    }
  }
}
```

UI: show `pricing.total` as the sale price, strike through `pricing.original_total`, and show `discount.label`. On upgrade, show `checkout.payable` as due now and `checkout.credit.amount` as unused-day credit.

## 3. Subscribe

`POST /api/v1/mentee/plans/subscribe/{id}`  
Auth: mentee bearer token.

Creates a Razorpay order for **`checkout.payable`** (plan discount already in `pricing.total`, leftover-day credit already subtracted). Response `data.pricing` is the new plan breakdown; `data.checkout` is the upgrade quote. `data.amount` is rupees; `data.amount_paise` is for Checkout.

Free plans and upgrades with `payable` under ₹1 activate immediately (HTTP 201) with an invoice. The new `starts_at` / `expires_at` begin today; previous plan session usage does **not** carry over.

## 4. Verify payment

`POST /api/v1/mentee/plans/subscribe/{id}/verify`

Body: `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`.

Uses the checkout snapshot stored at order time (does not recompute leftover days). Invoice `pricing.discount` and `pricing.credit` are snapshots of what was applied at purchase.

## 5. Web

Mentee **Plans** page uses the same breakdown, including unused-day credit on other plans. Checkout posts to `/mentee/plans/{id}/subscribe` — no extra fields; discount and leftover-day credit are automatic.
