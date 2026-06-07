# User Submission Program Current Year Filter

## Business Behavior

The Program ID dropdown on the User Submission page only loads KPI Workplans from the current calendar year.

This applies to:
- The initial Add/Edit Submission modal Program ID list.
- The MacframeGA preview modal Program ID list.
- The AJAX Program ID reload when the user's job level is resolved.

KPI Workplans from previous years or future years must not be offered as selectable Program ID options on `resources/views/pages/submission/user.blade.php`.

## Touched Modules

- `app/Services/SubmissionService/SubmissionServiceImpl.php`
  - `getUserPageData()` filters `$workplans` by `year = now()->year`.
  - `getProgramsByJobLevel()` filters AJAX results by `year = now()->year` before applying role and KPI type filters.
- `resources/views/pages/submission/user.blade.php`
  - Uses `$workplans` for initial dropdown rendering and `userSubmission.programs` for dynamic reloads.
- `routes/web.php`
  - `userSubmission.programs` remains the data source for job-level-based Program ID loading.

## Access Rules

The current-year filter is applied before the existing access scope:
- Admin users may see all divisions for the current year only.
- Non-admin users may see only their own division workplans for the current year.

## Testing

Covered by `tests/Feature/Services/SubmissionServiceTest.php`:
- Admin page data excludes previous-year and future-year workplans.
- Non-admin page data excludes previous-year and future-year workplans and remains division-scoped.
- Admin Program ID AJAX data excludes previous-year and future-year workplans.
- Non-admin Program ID AJAX data excludes previous-year and future-year workplans and remains division-scoped.
