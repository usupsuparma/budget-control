# Transaction Approval and LPJ Status Workflow

Last updated: 2026-04-24

## Purpose

This document defines the current transaction approval and LPJ workflow status behavior. Keep this document updated whenever transaction approval, LPJ submission, LPJ approval, file preview, or status badge behavior changes.

## Transaction Status Codes

The canonical transaction statuses are defined in `app/Models/Transaction.php`.

| Code | Constant | Meaning |
| --- | --- | --- |
| `0` | `STATUS_SUBMISSION` | New submission/draft state before or during initial submission flow. |
| `1` | `STATUS_PROGRESS` | Transaction approval is still in progress. At least one approval may have happened, but the chain is not complete. |
| `2` | `STATUS_APPROVED` | Transaction approval is fully completed by all approvers. |
| `3` | `STATUS_PAID` | LPJ (Laporan Pertanggungjawaban) has been submitted and fully approved. |
| `4` | `STATUS_COMPLETED` | Final operational state, set via external Webhook API after payment/processing is confirmed. |
| `5` | `STATUS_REJECTED` | Transaction or LPJ was rejected. |
| `-1` | `STATUS_CANCELLED` | Transaction was cancelled. |

## Approval Transaction Rules

- Submitting a transaction for approval sets `transactions.status` to `1` (`STATUS_PROGRESS`) and `transactions.status_approval` to `pending`.
- Partial approval keeps `transactions.status` as `1` (`STATUS_PROGRESS`) and updates `transactions.status_approval` to `in_progress`.
- Final approval sets `transactions.status` to `2` (`STATUS_APPROVED`) and `transactions.status_approval` to `approved`.
- Auto-approval must also set `transactions.status` to `2` (`STATUS_APPROVED`).
- Rejection sets `transactions.status` to `5` (`STATUS_REJECTED`) and `transactions.status_approval` to `rejected`.

Primary implementation:
- `app/Services/ApprovalTransactionService/ApprovalTransactionServiceImpl.php`
- `app/Services/SubmissionService/SubmissionServiceImpl.php`
- `app/Models/Transaction.php`

## LPJ Eligibility Rules

LPJ can be submitted when transaction approval is complete.

Allowed transaction statuses for LPJ submission:
- `2` (`STATUS_APPROVED`)
- `3` (`STATUS_PAID`) for re-submission or historical data

The source of truth for this check is `Transaction::canSubmitLpj()`.

Primary implementation:
- `app/Models/Transaction.php`
- `app/Services/LpjService/LpjServiceImpl.php`
- `resources/views/pages/submission/user.blade.php`
- `resources/views/pages/submission/due_date.blade.php`

## LPJ Submission and Approval Rules

- LPJ submission creates a `transaction_lpj_submissions` record.
- Uploaded proof files are stored on the `public` disk under `lpj_proofs`.
- LPJ submission status starts as `pending`, then moves to `in_progress` once the approval chain is created.
- Final LPJ approval sets `transaction_lpj_submissions.status_approval` to `approved`.
- Final LPJ approval sets the parent transaction status to `3` (`STATUS_PAID`).
- LPJ rejection sets `transaction_lpj_submissions.status_approval` to `rejected` and stores the rejection reason.

## External Webhook (Status 4)

Transactions can be moved to the final `COMPLETED` (4) state via an external API call. This is typically used by finance or external systems to confirm the cycle is 100% finished.

- **Endpoint:** `POST /api/v1/webhook/transaction/complete`
- **Payload:** `{ "id": [Transaction ID] }`
- **Rule:** Only transactions currently in status `3` (`PAID`) can be moved to `4` (`COMPLETED`).

## LPJ Proof File Preview

LPJ proof files are previewed through an authenticated route instead of relying on `public/storage` symlink availability.

Route:
- `userSubmission.lpj.proof`
- URL pattern: `transactions/user/lpj/{lpjId}/proof`

Preview behavior:
- `jpg`, `jpeg`, and `png` render as images.
- `pdf` renders in an iframe.
- Other supported file types should fall back to an open/download link.

Primary implementation:
- `app/Models/TransactionLpjSubmission.php`
- `app/Http/Controllers/SubmissionController.php`
- `resources/views/pages/submission/user.blade.php`
- `resources/views/pages/submission/approval.blade.php`

## UI Rules

- The LPJ button must use `can_submit_lpj` from the backend when available.
- UI fallback may allow statuses `2` and `3`, but the backend service remains authoritative.
- Status badges must show:
  - `1` as `Progress`
  - `2` as `Approved`
  - `3` as `Paid`
  - `4` as `Completed`
  - `5` as `Rejected`

## Verification Notes

Current known test caveat:
- `php artisan test` fails before running application tests because `tests/Unit/Services/DashboardServiceTest.php` calls Pest's `uses()` while the active runner reports it as undefined.
- Until test bootstrap is fixed, validate touched PHP files with `php -l` and run focused manual workflow checks for approval and LPJ.
