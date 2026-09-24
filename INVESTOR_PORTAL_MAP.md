# Investor Portal: quick map for changes and project defense

You do not need to memorize file names. Keep this note and use the feature name to find the right code.

## The simple flow

```text
Browser URL -> routes/web.php -> controller method -> database query/business rules -> Blade view
```

Example: `/portal/investor` is registered in `routes/web.php` and calls `InvestorDashboardController::index()`. That method loads the signed-in investor's bookings and related data, then returns `resources/views/investor/dashboard.blade.php`.

## Investor-facing pages

| What you want to change | Screen (what investor sees) | Backend (data/rules) | Route |
|---|---|---|---|
| Dashboard, booked flats, payment summary, reserve controls, construction update | `resources/views/investor/dashboard.blade.php` | `InvestorDashboardController::index()` at line 21 | `routes/web.php`, route `investor.dashboard` at line 157 |
| Payment history | `resources/views/investor/ledger.blade.php` | `InvestorDashboardController::ledger()` at line 150 | `routes/web.php`, `investor.ledger` at line 159 |
| Investor's documents and preview/download buttons | `resources/views/investor/documents.blade.php` | `InvestorDashboardController::documents()` at line 153; preview/download in `InvestorDocumentController.php` at lines 103 and 111 | `routes/web.php`, lines 160, 164-165 |
| A payment invoice/receipt | `resources/views/investor/invoice.blade.php` | `InvestorDashboardController::invoice()` at line 610 | `routes/web.php`, `investor.invoice` at line 171 |
| Notifications and mark-as-read | `resources/views/investor/notifications.blade.php` | `InvestorDashboardController::notifications()` at line 156; mark-read methods at lines 159 and 162 | `routes/web.php`, lines 161-163 |
| Payment method / gateway screen | `resources/views/investor/payment-gateway.blade.php` | `InvestorDashboardController::paymentGateway()` at line 289 and `processGatewayPayment()` at line 342 | `routes/web.php`, lines 169-170 |
| Reserve a flat | Button/form is in `dashboard.blade.php` | `InvestorDashboardController::reserve()` at line 166 | `routes/web.php`, `investor.reserve` at line 166 |
| Pay an installment | Payment form is in `dashboard.blade.php` | `InvestorDashboardController::pay()` at line 504 | `routes/web.php`, `investor.pay` at line 168 |
| Common sidebar, top bar, logout | `resources/views/layouts/investor.blade.php` | — | Links in the layout use named routes |
| Investor portal colors and responsive styling | `resources/css/app.css` (search for `.investor-portal-body`) | — | — |

Line numbers can move when code changes. Search for the method name or route name to find it again.

## Login and registration

- Screens: `resources/views/auth/investor-login.blade.php`, `investor-register.blade.php`, and `investor-otp.blade.php`.
- Login/register handling: `app/Http/Controllers/InvestorAuthController.php`.
- URLs and route-to-controller mapping: `routes/web.php`, search for `/investor/login`, `/investor/register`, or `investor.otp`.

## Admin pages related to investors

- Admin uploads documents: `resources/views/admin/investor-documents.blade.php`.
- Admin investor document upload/preview/download behavior: `app/Http/Controllers/InvestorDocumentController.php`.
- Admin reviews payment and submitted identity files: `resources/views/admin/investor-management.blade.php` and `app/Http/Controllers/AdminInvestorController.php`.

## What to show in a defense

Pick one feature and show these three places in order:

1. **Route:** In `routes/web.php`, search the route name, such as `investor.dashboard`.
2. **Logic:** Open the controller method named by that route. For the dashboard, show `InvestorDashboardController::index()` and its investor-scoped booking query (`where('user_id', $user->id)`).
3. **Screen:** Open the Blade view returned by that method, such as `resources/views/investor/dashboard.blade.php`.

For payments, show the relevant route, then `pay()` or `processGatewayPayment()`, then the payment form/gateway view. For documents, show the document route, `InvestorDocumentController::preview()` or `download()`, then `documents.blade.php`.

Useful searches from the project folder:

```powershell
rg -n "investor.dashboard|investor.pay|investor.documents" routes/web.php
rg -n "function index|function pay|function processGatewayPayment" app/Http/Controllers/InvestorDashboardController.php
rg -n "LIVE CONSTRUCTION UPDATE|Make a payment|Payment History" resources/views/investor/dashboard.blade.php
```

## Where the portal calculations happen

Most calculations are in `app/Http/Controllers/InvestorDashboardController.php`; the Blade files display the values they receive.

| Calculation shown in the portal | Where the formula/rule lives | What it means |
|---|---|---|
| Total paid for the investor | `index()` around line 82 | Adds this investor's payments whose status is `paid`. |
| Per-booking paid, remaining, and paid percentage | `index()` around lines 88-99 | `remaining = max(0, booking price - paid)`; `paid % = min(100, round(paid / price * 100, 1))`. The dashboard bar and Paid percentage display are in `dashboard.blade.php` around lines 218-222. This is payment completion, not construction progress. |
| Price, 15% reservation deposit, monthly installment, first due date | `reserve()` around lines 188-202 | Price comes from the project's `total_budget` (fallback BDT 5,000,000). Deposit is 15%; initial monthly amount is the remaining 85% divided by 12, 24, or 36 months; first installment date is 30 days after reservation. |
| Payment gateway amount and remaining balance | `paymentGateway()` around lines 303-328 | Calculates paid and remaining, then chooses deposit, one installment, custom amount, or full settlement based on payment intent/type. |
| Amount due in the pay flow | `pay()` around lines 523-558 | Finds the investor's own booking, calculates remaining balance, then selects full balance, a custom amount, or one monthly installment. |
| Upcoming/overdue alerts | `index()` around lines 102-140 | Compares each booking's next payment date with today; only alerts due within 7 days or already overdue. |
| Missed installment count / forfeiture | `refreshMissedInstallments()` around lines 617-643 | Calculates months overdue; after 3 missed installments, changes the booking status to `forfeited`. |
| Available-unit grid | `index()` around lines 55-78 | Builds 10 floors × 4 units (A-D) and marks units with an active booking as reserved. |
| Construction progress percentage | `ProjectProgressService::percentage()` around lines 9-19; called from `index()` around lines 42-44 | Averages task progress values. The investor dashboard no longer displays this percentage, but this backend calculation still exists. |

**Installment detail to be aware of:** reservation initially sets each installment to `(price × 85%) ÷ months` because the 15% deposit is paid first. If an investor changes the plan later in `pay()`, the saved monthly amount is recalculated as `price ÷ months` (around lines 540-549). If asked about this in a defense, explain which path is being discussed; the two formulas currently differ.

For a defense, state the formula in plain language, then show the matching controller lines and the Blade line that displays it. Example: “Paid percentage is paid amount divided by booking price, multiplied by 100; the calculation is in `bookingSummaries`, and the dashboard displays it in the payment summary.”
