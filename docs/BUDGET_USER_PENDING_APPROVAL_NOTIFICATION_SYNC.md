# Budget User Pending Approval Notification Sync

## Business Behavior

The Budget User Approval tab must show the same actionable task count as its pending approval badge. A user must not see a Budget User approval notification when there is no `workplan_budget_items` approval detail that the user can approve next.

## Data Contract

The pending approval endpoint remains:
- `GET /workplan-budget-item-approval/pending`
- Route name: `wbi.approval.pending`

The endpoint now returns only approval requests whose module table is `workplan_budget_items`, whose request status is `pending`, and whose current pending detail belongs to the logged-in approver employment.

Rows whose referenced `workplan_budget_items` record cannot be loaded are excluded from the response and from `count`.

## Notification Cleanup

When the pending approval list is loaded, stale task notifications are deleted for the current employee when they use:
- category: `approval`
- title: `Permintaan Approval Workplan Budget`
- reference type: `workplan_budget_item_approval`

Legacy task notifications with the same title but missing reference columns are also treated as stale unless they point to an active actionable item.

Requester result notifications such as `Workplan Budget Disetujui` or `Workplan Budget Ditolak` are not removed by this cleanup because they use different titles.

## Touched Modules

- `app/Services/WorkplanBudgetItemApprovalService.php`
  - Scopes pending approval query to `workplan_budget_items`.
  - Excludes orphaned budget item approval rows before computing `count`.
  - Deletes stale Budget User approval task notifications for the current employee.
- `app/Services/NotificationService.php`
  - Adds a helper to delete stale task notifications while preserving active references.
- `resources/views/pages/budget/budget-user.blade.php`
  - Continues to use `wbi.approval.pending`; no Blade contract change.

## Testing

Covered by `tests/Feature/Services/WorkplanBudgetItemApprovalServiceTest.php`:
- Pending approvals exclude approval requests from other modules and delete stale referenced or legacy task notifications.
- Active `workplan_budget_items` pending approval remains visible and its notification is preserved.
