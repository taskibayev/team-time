# Problems and fixes

## Scaffold ownership

Composer ran in a container as root, leaving generated files unwritable by the workspace user. Ownership was corrected with a short-lived Alpine container. Subsequent patch operations verified the fix.

Additional verified defects found during quality checks are recorded below.

## PHP 8.3 dependency resolution

The initial Composer resolution used PHP 8.5 from the Composer image and selected packages requiring PHP 8.4. Composer's platform was pinned to PHP 8.3 and incompatible Doctrine, UX, and PHPUnit packages were resolved to PHP 8.3-compatible releases. The Docker test runtime reported PHP 8.3.36 and PHPUnit passed.

## PHPStan object-manager bootstrap

The first PHPStan run failed because its Doctrine object-manager loader referenced the obsolete `config/bootstrap.php` path. It now loads `tests/bootstrap.php` and boots the kernel explicitly.

## Missing Twig runtime components

The HTTP smoke test returned 500 because Twig's `asset()` helper and CSRF token manager were unavailable. `symfony/asset` and `symfony/security-csrf` were added and CSRF protection was enabled. The fix is verified by successful HTTP responses in the final smoke test.

## Custom period dates were ignored

Changing the From or To field while the period preset remained `Today` caused the server to ignore the entered dates. Both period forms now switch the preset to `Custom` as soon as either date is changed, so the submitted range controls the report.

The From and To controls are only displayed for the `Custom` preset. Their visibility is initialized on the server and kept in sync in the browser when the preset changes.

The employee-page Apply period button stays in the fourth, rightmost grid column when custom date fields are toggled, preventing layout movement. On narrow screens it returns to the normal single-column flow.

## Report period parameters lost their timezone

Doctrine inferred report boundary parameters as timezone-less immutable datetimes even though time records use timezone-aware columns. PostgreSQL could therefore interpret Aqtau wall-clock boundaries as UTC and exclude overlapping records. `ReportService` now binds both boundaries explicitly as `DATETIMETZ_IMMUTABLE`; a PostgreSQL-backed integration test verifies clipping a record to the requested period.

## Final verification

- `docker compose --profile test run --rm --build test`: PHPUnit 12.5.43 passed 3 tests with 7 assertions on PHP 8.3.36; PHPStan level max reported no errors.
- `docker compose up --build -d app`: migrations were current and the idempotent seed skipped existing data on restart.
- HTTP smoke checks returned 200 for `/timers`, `/employees`, and `/reports`.
