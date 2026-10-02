# Antigravity Master Prompt

## InvoxPakistan — Subscription, Transactions, FBR Credentials, Invoice Restriction & Email Automation

You are working on an **existing Laravel project**.

Project:

* Path: `C:\laragon\www\InvoxPakistan`
* Laravel: 12.62
* Database: MySQL
* Frontend: Bootstrap + Blade
* Existing code must be **refactored/extended**, not replaced unnecessarily.
* Do not create unnecessary services, packages, modules, or architecture.
* Prefer existing Models, Controllers, Migrations, Mailables, Jobs, Commands and Blade views where possible.
* Keep the implementation simple, maintainable and compatible with the existing project.

---

# MOST IMPORTANT INSTRUCTION

Implement this task **chunk by chunk**.

I will give you:

* Chunk 0
* Chunk 1
* Chunk 2
* Chunk 3
* Chunk 4

Do NOT implement future chunks before I provide them.

For every chunk:

1. First inspect the existing code related to that chunk.
2. Understand how the existing implementation works.
3. Reuse existing code wherever possible.
4. Make only the changes required for that chunk.
5. Do not break existing functionality.
6. Do not redesign unrelated parts of the project.
7. Do not add unnecessary features.
8. Do not ask me unnecessary questions if the requirement is already defined in this document.
9. If something is genuinely ambiguous, inspect the existing code first and make the safest implementation based on the rules below.
10. After implementation, test the affected functionality.
11. Fix any errors found.
12. Clearly report:

* Files changed
* What was changed
* Database changes
* Commands executed
* Tests performed
* Any remaining issue

Before starting implementation, read and understand:

`rules.md`

Create/update `rules.md` in the project root during **Chunk 0**.

This file is the permanent source of truth for all following chunks.

---

# CHUNK STRUCTURE

## Chunk 0

Analyze existing project + create `rules.md` + prepare architecture.

## Chunk 1

Company Create/Edit + Subscription + FBR Credentials.

## Chunk 2

Transaction creation + status lifecycle + payment flow.

## Chunk 3

Scheduler + reminder/due emails + invoice restriction.

## Chunk 4

Complete integration testing + bug fixing + final cleanup.

Do not combine these chunks.

---

# BUSINESS RULES — SOURCE OF TRUTH

## 1. COMPANY CREATION

Company Create should contain **only basic company information**.

Remove from Company Create/Store:

* Subscription Information
* FBR Credentials

These will be handled from Company Edit/Update.

After successful company creation:

* Send Company Creation Welcome Email.

Do not add subscription or FBR information to the create process.

---

# 2. SUBSCRIPTION

Subscription is managed from Company Edit/Update.

Subscription has:

* Status: Active / Inactive
* Start Date
* Type
* Amount

Types:

* Monthly
* Yearly

The UI may use existing project naming such as:

* 30 Days
* 365 Days

But the actual date calculation must use **calendar periods**, NOT exact 30/365-day arithmetic.

---

# 3. SUBSCRIPTION START DATE

Start Date is required when:

1. Creating/activating a new subscription.
2. Reactivating an Inactive subscription.

Start Date:

* Can be today.
* Can be a future date.
* Cannot be a past date.

For an already active subscription:

* Start Date should normally be read-only.
* Do not allow accidental modification of the active subscription's original Start Date.

---

# 4. MONTHLY DATE CALCULATION

Monthly subscription does NOT mean simply:

`Start Date + 30 days`

Instead:

`End Date = same date in next calendar month - 1 day`

If the same date does not exist in the next month, use the last valid day of that month.

Examples:

```text
01-Oct-2026 → 31-Oct-2026

05-Oct-2026 → 04-Nov-2026

31-Jan-2027 → 28-Feb-2027

31-Jan-2028 → 29-Feb-2028
```

Next transaction starts:

`Previous End Date + 1 day`

---

# 5. YEARLY DATE CALCULATION

Yearly subscription does NOT mean simply:

`Start Date + 365 days`

Instead:

`End Date = same date next year - 1 day`

Example:

```text
05-Oct-2026 → 04-Oct-2027
```

Next transaction starts:

```text
05-Oct-2027
```

Leap-year behavior must be handled safely.

---

# 6. INITIAL TRANSACTION CREATION

When a subscription is activated:

### Transaction #1

Automatically create the first transaction.

Transaction #1:

* Start Date = Subscription Start Date
* Type = Subscription Type
* Amount = manually entered subscription/payment amount
* Status = Paid

This represents the first successful subscription/payment period.

### Transaction #2

Automatically create exactly **ONE future transaction**.

Transaction #2:

* Start Date = Transaction #1 End Date + 1 day
* End Date = calculated according to subscription type

Its status should be determined according to the current date:

* Future period → Due
* If its relevant period has already passed → Overdue

Do NOT create multiple future transactions.

---

# 7. ONLY ONE FUTURE TRANSACTION

This is extremely important.

At any time there can be:

* Historical Paid transactions
* One current unpaid transaction

There must NOT be a chain like:

```text
Nov Due
Dec Due
Jan Due
Feb Due
```

Only one future/unpaid transaction should exist.

The next transaction is created **only after the latest unpaid transaction is successfully paid**.

Example:

```text
August Paid
↓
September Due
↓
September Paid
↓
October Due
↓
October Paid
↓
November Due
```

If September is not paid:

```text
August Paid
↓
September Due
↓
September Pending
↓
September Overdue
```

There should NOT already be an October Due transaction.

---

# 8. TRANSACTION STATUS

There are exactly four statuses:

```text
Paid
Due
Pending
Overdue
```

Meaning:

### Paid

Payment successfully completed.

### Due

Future renewal transaction.

Payment window has not started yet.

### Pending

Reminder/payment window has started.

### Overdue

Transaction End Date has passed and payment was not completed.

---

# 9. STATUS LIFECYCLE

Normal lifecycle:

```text
Due
 ↓
Pending
 ↓
Overdue
 ↓
Paid
 ↓
New Due
```

Important:

* Due → Pending = 7 days before End Date.
* Pending remains Pending on End Date.
* Pending → Overdue = day after End Date if still unpaid.
* Overdue → Paid = successful payment.

---

# 10. 7-DAY REMINDER

The 7-day reminder is based on the transaction's **END DATE**.

NOT Start Date.

Example:

```text
Transaction:
01-Nov-2026 → 30-Nov-2026
```

On:

```text
23-Nov-2026
```

which is 7 days before End Date:

```text
Due → Pending
```

At the same time:

* Send 7-Day Reminder Email.
* Show Pay Now.

Email failure must NOT stop the status transition.

---

# 11. DUE DATE EMAIL

On the transaction's End Date:

If transaction is still unpaid:

* Send Subscription Due Email.
* Status remains Pending on that date.

Example:

```text
End Date = 30-Nov-2026
```

On:

```text
30-Nov-2026
```

send Due Email.

The Due Email must be sent only once for that transaction.

If email fails:

* Transaction status logic must still continue.

---

# 12. OVERDUE

The day AFTER End Date:

If payment has not been completed:

```text
Pending → Overdue
```

Example:

```text
End Date = 30-Nov-2026

30-Nov:
Pending + Due Email

01-Dec:
Overdue + Invoice Restriction
```

---

# 13. EMAIL FAILURE MUST NOT BREAK SUBSCRIPTION LOGIC

This is critical.

Do NOT implement:

```text
if email succeeds
    change status
```

Instead:

```text
change status
attempt/send email
```

or otherwise ensure the transaction state is independent from email delivery.

SMTP problems must never leave the subscription in the wrong state.

---

# 14. EMAIL DUPLICATE PROTECTION

Each transaction must receive:

### 7-Day Reminder

Maximum once.

### Due Email

Maximum once.

Use appropriate database flags, timestamps, or a reliable existing email-log mechanism.

Do NOT send the same automated email repeatedly every time the scheduler runs.

---

# 15. PAYMENT

Payment is manual.

There is NO payment gateway requirement.

Admin uses:

`Pay Now`

Pay Now is available only for the **latest unpaid transaction** when its status is:

* Pending
* Overdue

Pay Now is NOT available for:

* Paid
* Due

Payment information includes:

* Amount
* Start Date
* Type
* Payment Screenshot

Payment amount is manually entered.

Do NOT automatically calculate the payment amount from subscription type.

---

# 16. SUCCESSFUL PAYMENT

When an unpaid transaction is successfully paid:

```text
Pending → Paid
```

or:

```text
Overdue → Paid
```

Then:

1. Save payment information.
2. Save payment screenshot.
3. Send Payment Thank You Email.
4. Create exactly ONE next transaction.

The newly created transaction:

* Starts from previous transaction End Date + 1 day.
* End Date is calculated according to the current subscription type.
* Status is:

  * Due if its period is still future.
  * Overdue if its relevant period has already passed.

Do NOT create another transaction if the transaction was already Paid.

Do NOT send duplicate Thank You emails.

Do NOT create duplicate next transactions.

---

# 17. MULTIPLE OVERDUE TRANSACTIONS

Multiple Overdue transactions should NOT normally exist.

Reason:

Only one unpaid/future transaction exists at a time.

The next transaction cannot be created until the current unpaid transaction is paid.

Therefore:

```text
Nov Overdue
```

can exist without:

```text
Dec Overdue
```

already existing.

If an old transaction is eventually paid, only then create the next transaction.

---

# 18. INACTIVE SUBSCRIPTION

When:

```text
Active → Inactive
```

Do NOT delete historical records.

Keep:

* Paid transactions
* Overdue transactions

Delete future/unpaid transactions that are:

* Due
* Pending

No new future transaction should be created while subscription is Inactive.

---

# 19. INVOICE RESTRICTION

Invoice functionality must be blocked when:

### Condition 1

Subscription is Inactive.

OR

### Condition 2

Latest unpaid transaction is Overdue.

Therefore:

```text
Inactive → Invoice Blocked

Overdue → Invoice Blocked
```

When Overdue is successfully paid:

```text
Overdue → Paid
Invoice Unblocked
```

When subscription becomes Active again:

* Invoice access can resume according to the current transaction/subscription state.

Do not invent another invoice restriction period.

---

# 20. REACTIVATION

When:

```text
Inactive → Active
```

Admin must provide:

* New Start Date
* Type
* Amount

Start Date:

* Today or future.
* Past date is not allowed.

The reactivation starts a new cycle from the new Start Date.

Old:

* Paid
* Overdue

transactions remain as historical records.

Create:

### New Transaction #1

Paid

### New Transaction #2

Due or Overdue depending on current date.

---

# 21. SUBSCRIPTION TYPE CHANGE

Historical Paid transactions must NOT be modified.

For the current/future transaction:

### If status = Due

Keep:

```text
Status = Due
```

but recalculate its End Date according to the new subscription Type.

### If status = Pending

When Type changes:

```text
Pending → Due
```

Then recalculate dates according to the new Type.

### If status = Overdue

When Type changes:

```text
Overdue → Due
```

Then recalculate dates according to the new Type.

The transaction Start Date remains the same.

Example:

Current transaction:

```text
01-Nov-2026 → 30-Nov-2026
Pending
```

Change:

```text
Monthly → Yearly
```

Result:

```text
01-Nov-2026 → 31-Oct-2027
Due
```

The Start Date does not change.

Historical Paid records remain untouched.

---

# 22. FBR CREDENTIALS

FBR Credentials are managed only from:

`Company Edit/Update`

Not Company Create.

FBR status:

```text
Active
Inactive
```

When Active:

* Production token is usable.

When Inactive:

* Sandbox token is usable.

Only the selected token should be usable at one time.

Existing field:

```text
fbr_token_sandbox
```

must be reused where appropriate.

Do not break the existing FBR invoice functionality.

---

# 23. EMAIL EVENTS

There are exactly four required automated email events:

| Event                       | Email                   |
| --------------------------- | ----------------------- |
| Company Created             | Welcome Email           |
| 7 days before End Date      | 7-Day Reminder Email    |
| End Date reached and unpaid | Subscription Due Email  |
| Successful Payment          | Payment Thank You Email |

Do not add unnecessary email types.

---

# 24. SCHEDULER

Laravel Scheduler should automatically check subscription transactions.

It should handle:

* 7-day reminder
* Due email
* Pending → Overdue

The scheduler must be safe to run repeatedly.

Running it multiple times must NOT:

* duplicate transactions
* duplicate reminder emails
* duplicate due emails
* incorrectly change Paid transactions
* create multiple future transactions

Use proper conditions and duplicate protection.

---

# 25. DATABASE SAFETY

Do not duplicate records because of:

* Scheduler running twice
* User refreshing a page
* Payment form resubmission
* Re-saving an already-paid transaction
* Subscription edit being submitted twice

Use database transactions/validation/unique constraints where appropriate.

Do not destroy existing production data.

Do not run destructive migrations unless absolutely required and clearly justified.

---

# 26. EXISTING FBR FUNCTIONALITY

Existing FBR functionality must continue working.

Known existing area:

```text
resources/views/company/invoices/create.blade.php
```

Existing functionality includes:

* Province
* HS
* Rate
* UoM
* SRO
* FBR token
* Invoice save
* FBR response
* QR

Do not rewrite this unnecessarily.

Only modify invoice authorization/restriction where required by the subscription rules.

---

# 27. CODE QUALITY

Use the existing project style.

Prefer:

* Existing Models
* Existing Controllers
* Existing routes
* Existing Blade components
* Existing Mailables
* Existing Jobs
* Existing Commands

Avoid unnecessary:

* Service classes
* Repositories
* Interfaces
* Packages
* APIs
* Frontend frameworks
* Architecture rewrites

If existing architecture already has a suitable implementation, extend it.

---

# 28. VALIDATION

Validate all important inputs.

Especially:

### Subscription Start Date

Must be:

```text
today or future
```

Never:

```text
past
```

### Subscription Type

Must be a valid configured type.

### Amount

Must be valid according to existing application rules.

### Payment Screenshot

Must use appropriate existing file upload/security rules.

Do not weaken existing validation.

---

# 29. TRANSACTION DATE CALCULATION

Create one reliable date-calculation implementation and reuse it.

Do not implement different date formulas in multiple controllers.

The logic must correctly handle:

* 28-day February
* 29-day February
* 30-day months
* 31-day months
* leap years
* month-end dates
* year transitions

Most importantly:

**Do NOT use simple `+30 days` or `+365 days`.**

---

# 30. EXAMPLE COMPLETE FLOW

Example:

Subscription:

```text
Start Date = 01-Oct-2026
Type = Monthly
Amount = 1000
```

Create:

```text
Transaction 1:
01-Oct-2026 → 31-Oct-2026
Paid

Transaction 2:
01-Nov-2026 → 30-Nov-2026
Due
```

On:

```text
23-Nov-2026
```

System:

```text
Due → Pending
```

Send:

```text
7-Day Reminder
```

Pay Now becomes available.

If not paid:

On:

```text
30-Nov-2026
```

send:

```text
Subscription Due Email
```

Status:

```text
Pending
```

On:

```text
01-Dec-2026
```

status:

```text
Overdue
```

Invoice:

```text
Blocked
```

Admin pays:

```text
Overdue → Paid
```

Then:

```text
Payment Thank You Email
```

Create:

```text
Transaction 3:
01-Dec-2026 → 31-Dec-2026
Due
```

Invoice:

```text
Unblocked
```

---

# 31. IMPORTANT EDGE CASE

Suppose:

```text
November transaction becomes Overdue.
```

Admin pays it much later, for example:

```text
January
```

Only after that payment should the next transaction be created.

If the newly generated transaction's period is already past according to the current date:

```text
Create it as Overdue
```

If its period is future:

```text
Create it as Due
```

Never blindly create every new transaction as Due.

---

# 32. CHUNK 0 REQUIREMENTS

When I give you Chunk 0:

1. Inspect the entire existing project structure.
2. Locate:

   * Company model
   * Company controller
   * Company create view
   * Company edit view
   * Subscription-related models
   * Transaction model
   * Transaction migration
   * Company migration
   * Subscription migration
   * Invoice controller
   * Invoice routes
   * Invoice authorization logic
   * Existing Mailables
   * Existing email configuration
   * Existing Jobs
   * Existing Console Commands
   * Existing Scheduler configuration
3. Do not modify unrelated functionality.
4. Create/update:

```text
rules.md
```

at the project root.

5. Put the complete business rules from this prompt into `rules.md`.
6. Add a short implementation checklist.
7. Do NOT start the actual feature implementation yet.
8. Report your findings and wait for Chunk 1.

---

# 33. CHUNK 1 REQUIREMENTS

When I give you Chunk 1:

Implement:

### Company Create

Remove:

* Subscription information
* FBR credentials

Keep basic company information.

Add Company Welcome Email trigger.

### Company Edit

Implement/manage:

* Subscription Active/Inactive
* Start Date
* Type
* Amount
* FBR Active/Inactive
* Production token
* Sandbox token

Implement validation.

Implement:

* New activation
* Inactive → Active reactivation
* Active → Inactive

Implement subscription type-change rules.

Implement deletion of:

* Due future transaction
* Pending future transaction

when subscription becomes Inactive.

Never delete:

* Paid
* Overdue

Do NOT yet implement scheduler/email lifecycle unless required for basic activation.

At the end:

* Test company create.
* Test company edit.
* Test activate.
* Test deactivate.
* Test reactivate.
* Test type change.
* Test FBR status.
* Test that existing FBR functionality is not broken.

Then report and wait for Chunk 2.

---

# 34. CHUNK 2 REQUIREMENTS

Implement:

### Transaction creation

* Initial Paid transaction.
* Exactly one future transaction.

### Date calculation

Implement correct Monthly/Yearly calendar calculations.

### Status

Implement:

* Paid
* Due
* Pending
* Overdue

### Payment

Implement:

* Pay Now
* Amount
* Start Date
* Type
* Payment screenshot
* Successful payment
* Payment Thank You Email trigger

### Next transaction

Create exactly one next transaction after successful payment.

### Duplicate protection

Prevent:

* duplicate payment
* duplicate next transaction
* duplicate Thank You email

### UI

Pay Now only when latest unpaid transaction is:

```text
Pending
OR
Overdue
```

No Pay Now for:

```text
Due
Paid
```

Then test the full transaction/payment flow.

Do NOT yet implement scheduler automation unless required to complete the transaction foundation.

Then report and wait for Chunk 3.

---

# 35. CHUNK 3 REQUIREMENTS

Implement Laravel Scheduler automation.

Scheduler must check transactions and perform:

### 7 days before End Date

If:

```text
status = Due
```

and today is exactly the 7-day reminder point:

```text
Due → Pending
```

Send:

```text
7-Day Reminder Email
```

Only once.

### End Date

If:

```text
status = Pending
```

and today is End Date:

Send:

```text
Subscription Due Email
```

Only once.

Keep:

```text
Pending
```

### Day after End Date

If:

```text
status = Pending
```

and End Date has passed:

```text
Pending → Overdue
```

### Invoice restriction

Block invoice access when:

```text
Subscription = Inactive
```

OR:

```text
Latest unpaid transaction = Overdue
```

Unblock after:

```text
Overdue → Paid
```

Make scheduler execution idempotent.

Email failure must not stop transaction state changes.

Test scheduler manually using appropriate Laravel commands/testing techniques.

Then report and wait for Chunk 4.

---

# 36. CHUNK 4 REQUIREMENTS

Perform final end-to-end testing.

Test at minimum:

## Scenario 1 — New Monthly Subscription

```text
01-Oct
↓
Paid
↓
01-Nov Due
```

## Scenario 2 — Reminder

```text
23-Nov
↓
Due → Pending
↓
Reminder Email
```

## Scenario 3 — Due Date

```text
30-Nov
↓
Due Email
↓
Pending
```

## Scenario 4 — Overdue

```text
01-Dec
↓
Overdue
↓
Invoice Blocked
```

## Scenario 5 — Overdue Payment

```text
Pay Now
↓
Paid
↓
Invoice Unblocked
↓
Next transaction created
```

## Scenario 6 — Inactive

```text
Active
↓
Inactive
```

Verify:

* Due/Pending future transaction removed.
* Paid remains.
* Overdue remains.
* Invoice blocked.
* No future transaction created.

## Scenario 7 — Reactivation

```text
Inactive
↓
Active
```

Verify:

* New Start Date required.
* Past Start Date rejected.
* New cycle starts correctly.
* New Paid + one future transaction created.

## Scenario 8 — Monthly Date Edge Cases

Test:

```text
31-Jan
28-Feb
29-Feb
30-day month
31-day month
```

## Scenario 9 — Yearly

Test:

```text
05-Oct-2026
→
04-Oct-2027
```

## Scenario 10 — Type Change

Test:

```text
Monthly Due
→
Yearly Due
```

and:

```text
Monthly Pending
→
Yearly Due
```

and:

```text
Monthly Overdue
→
Yearly Due
```

Verify Start Date stays unchanged.

## Scenario 11 — Email Failure

Simulate email failure and verify:

* Status still changes correctly.
* No transaction corruption.
* Scheduler remains safe.

## Scenario 12 — Duplicate Protection

Run scheduler multiple times.

Verify:

* No duplicate reminder.
* No duplicate due email.
* No duplicate transactions.

Verify payment submission cannot create duplicate next transactions.

---

# 37. FINAL CLEANUP

After all tests:

1. Remove dead code created during implementation.
2. Remove unnecessary duplicate logic.
3. Check routes.
4. Check migrations.
5. Check models.
6. Check controllers.
7. Check Blade views.
8. Check mailables.
9. Check scheduler.
10. Check invoice restriction.
11. Check validation.
12. Check database consistency.
13. Check Laravel logs for errors.
14. Do not modify unrelated modules.

Update:

```text
rules.md
```

with the final implementation status.

---

# 38. REPORT FORMAT AFTER EVERY CHUNK

After completing each chunk, use exactly this structure:

```text
CHUNK COMPLETED

1. Files Changed
- file
- file
- file

2. Database Changes
- change

3. Functionality Implemented
- item
- item
- item

4. Validation
- test
- result

5. Commands Run
- command

6. Issues Found
- issue or "None"

7. Status
READY FOR NEXT CHUNK
```

Do not provide unnecessary explanations.

---

# 39. DO NOT BREAK THESE RULES

Never:

* Create multiple future transactions.
* Use +30 days for monthly.
* Use +365 days for yearly.
* Delete Paid transactions.
* Delete Overdue transactions.
* Allow invoice access for an Inactive subscription.
* Allow invoice access while latest unpaid transaction is Overdue.
* Show Pay Now for Due.
* Show Pay Now for Paid.
* Send duplicate reminder emails.
* Send duplicate due emails.
* Send duplicate payment thank-you emails.
* Create duplicate next transactions.
* Let email failure break subscription status.
* Modify historical Paid transaction dates when changing subscription type.
* Rewrite unrelated existing modules.
* Replace working FBR functionality unnecessarily.
* Ask me to repeat business rules already defined in `rules.md`.

---

# 40. FINAL PRIORITY

If existing project code conflicts with these business rules:

1. Preserve unrelated existing functionality.
2. Follow these subscription/transaction business rules.
3. Modify the smallest possible amount of existing code.
4. Do not create unnecessary architecture.
5. Test before declaring the chunk complete.

The goal is a **working production-ready implementation in the existing InvoxPakistan Laravel project**, not a theoretical redesign.

Wait for each chunk and implement only the chunk provided.

---

# IMPLEMENTATION CHECKLIST

## Chunk 0: Architecture Analysis & Setup (COMPLETED)
- [x] Analyze existing project structure and identify key files.
- [x] Locate Company Model, Controllers, Requests, and Views.
- [x] Locate Subscription and Transaction Models, Migrations, and Domain Services.
- [x] Locate Invoice Controllers, Middleware, Policies, and Routes.
- [x] Locate Mailables (`CompanyWelcome`, `PaymentReminder`, `PaymentDue`, `PaymentThankYou`).
- [x] Locate Console Commands and Scheduler setup (`routes/console.php`).
- [x] Create/Update `rules.md` as permanent source of truth with business rules and checklist.

## Chunk 1: Company Create/Edit + Subscription + FBR Credentials (COMPLETED)
- [x] Refactor Company Create: Remove subscription and FBR credential input fields; preserve basic company info + login setup. Trigger `CompanyWelcome` email.
- [x] Refactor Company Edit: Move Subscription (Status, Start Date, Type, Amount) and FBR Credentials (Status, Sandbox/Production Tokens) management here.
- [x] Implement Validation: Start Date (today/future), Subscription Type, Amounts, FBR tokens.
- [x] Implement Subscription Status Transitions: Active/Inactive, Reactivation (with new Start Date, Type, Amount).
- [x] Implement Future Transaction Cleanup: Delete `Due`/`Pending` future transactions when subscription set to `Inactive` (preserve `Paid` & `Overdue`).
- [x] Preserve FBR functionality and verify existing tests/routes.

## Chunk 2: Transaction Creation + Status Lifecycle + Payment Flow (COMPLETED)
- [x] Implement calendar-based date calculations for Monthly (`same day next month - 1 day` or last valid day) and Yearly (`same day next year - 1 day`).
- [x] Refactor Initial Subscription Activation: Create Transaction #1 (`Paid`) and exactly ONE future Transaction #2 (`Due` or `Overdue` based on current date).
- [x] Enforce Single Future Transaction constraint.
- [x] Implement Admin `Pay Now` flow for latest unpaid transaction (when `Pending` or `Overdue`).
- [x] Implement Payment submission handling: Amount input, Start Date, Type, Screenshot upload, transition to `Paid`, send `PaymentThankYou` email.
- [x] Auto-generate next single transaction (`Due` or `Overdue`) from `Previous End Date + 1 day`.
- [x] Implement Duplicate Protection (prevent duplicate payments, duplicate emails, duplicate next transactions).

## Chunk 3: Scheduler + Email Automation + Invoice Restriction (COMPLETED)
- [x] Refactor Console Commands & Scheduler (`routes/console.php`) for Idempotent Execution.
- [x] 7-Day Reminder Logic: Transition `Due` -> `Pending` 7 days before End Date & send `PaymentReminder` email (maximum once).
- [x] End Date Logic: On End Date, if unpaid, send `PaymentDue` email (maximum once) while keeping status `Pending`.
- [x] Overdue Logic: Day after End Date, if unpaid, transition `Pending` -> `Overdue`.
- [x] Decouple email sending failures from subscription/transaction status state transitions.
- [x] Enforce Invoice Restriction: Block invoice creation/access when Subscription is `Inactive` OR latest unpaid transaction is `Overdue`. Unblock when `Paid` or `Active`.

## Chunk 4: E2E Integration Testing + Bug Fixing + Cleanup (COMPLETED)
- [x] Test Scenario 1: New Monthly Subscription lifecycle.
- [x] Test Scenario 2: 7-Day Reminder trigger & email.
- [x] Test Scenario 3: Due Date email trigger.
- [x] Test Scenario 4: Overdue status transition & invoice restriction blocking.
- [x] Test Scenario 5: Overdue payment, invoice unblocking, next transaction generation.
- [x] Test Scenario 6: Deactivation (future due/pending removal, paid/overdue retention, invoice block).
- [x] Test Scenario 7: Reactivation (new start date validation, new cycle start).
- [x] Test Scenario 8: Monthly date edge cases (Jan 31 -> Feb 28/29, leap years, 30/31 day months).
- [x] Test Scenario 9: Yearly subscription calculation (Oct 5, 2026 -> Oct 4, 2027).
- [x] Test Scenario 10: Subscription type changes on Due/Pending/Overdue transactions (Start Date unchanged).
- [x] Test Scenario 11: Email failure tolerance (status updates even if SMTP fails).
- [x] Test Scenario 12: Idempotency & Duplicate Protection verification.
- [x] Code Cleanup, route/migration/model audit, and final status update in `rules.md`.

