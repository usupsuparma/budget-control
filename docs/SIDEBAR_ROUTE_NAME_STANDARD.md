# Sidebar Route Name Standard

Last updated: 2026-04-24

## Purpose

Sidebar navigation must be resilient to URL/prefix changes. Use route names as the source of truth for links, active states, and collapse states.

## Rules

- Use `route('name')` for sidebar `href` values.
- Use `request()->routeIs('name.*')` for active menu checks.
- Use route name arrays for parent menus that contain multiple children.
- Avoid `Request::is()` and hardcoded path matching in sidebar views.
- Keep placeholder menu items inactive when they do not have dedicated route names yet.
- If a route belongs visually to a different menu than its route prefix suggests, define an explicit boolean guard to avoid opening the wrong parent menu.

## Current Implementation

Primary file:
- `resources/views/include/sidebar.blade.php`

Important route groups:
- Dashboard: `dashboard`, `dashboard.*`, `dash.executive`, `dash.executive.*`
- Company Policy: `company-policy.*`
- KPI: `sasaran-strategis.*`, `kpidivision.*`, `KPIDepartment.*`, `kpisection.*`
- Sales Plan: `production.*`, `marketing.*`
- Budget: `workplan.*`, `anggaran.*`, `budget-admin.*`, `budget.admin.*`, `budget-user.*`, `pengajuan.anggaran.*`, `budget-resume.*`, `userSubmission.dueDate`, `userSubmission.dueDateData`, `budget.submission.*`
- Transactions: `approvalSubmission.*` plus `userSubmission.*`, excluding Budget Due Date routes
- Settings: `master`, `master.*`, `users.*`, `code.*`, `setting.production.*`, `approval.*`, `auth.roles*`, `history`, `settingPriceVerificator.*`

## Notes

The Budget Due Date page uses `userSubmission.dueDate`, but it is visually part of the Budget menu. Sidebar logic must exclude it from the Transactions parent menu.
