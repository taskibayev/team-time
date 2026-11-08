# TeamTime — Technical Specification

Status: first-version specification compiled from the project discussion.
All application data must be fictional. Section 11 records disclosed implementation defaults for the first version; they can be revised later.

## 1. Objective and scope

Build a small company employee time-tracking web application from scratch with AI assistance. It must support employee management, starting and stopping work timers, reports for a selected period, and persistent storage across application restarts. No authentication or complex roles are required.

## 2. Technology stack

- PHP 8.3 and Symfony 7.4.
- PostgreSQL; local database in Docker Compose, hosted database in Neon.
- Server-rendered Twig templates with Symfony UX Turbo, AssetMapper and Stimulus. No separate SPA or dedicated JSON API is required.
- Stimulus handles the visible timer countdown/count-up; server timestamps are authoritative.
- PHPUnit for unit tests, Symfony Test Pack for integration and functional tests, Symfony Panther for browser tests.
- PHPStan at `level: max`. Checks must pass without a baseline or error suppressions.

## 3. Employees

- Fields: first name, last name, position and department.
- Employees are stored in PostgreSQL.
- Employees can be added and edited.
- The first-name/last-name combination must be unique. Either field may repeat individually.
- Employee deletion and archiving are outside this version.
- Position and department options are initially stored in arrays with stable identifiers.
- Access these catalogs through interfaces with array-backed implementations, allowing later database-backed implementations without changing forms or business logic.
- Store catalog identifiers on employee records; validate selected identifiers on the server.

## 4. Descriptions and time records

- A task is not a separate entity.
- Suggested descriptions are initially stored in an array and exposed through an interface with an array-backed implementation, as with the other catalogs.
- Users can select a suggested description. Store a copy of the description text on the time record, not a reference to the catalog. Later catalog changes must not change historical records.
- A description is mandatory and has a maximum length of 255 characters.
- Trim leading and trailing whitespace. Reject an empty or whitespace-only description. Validate on the server and display errors in the form.
- Each employee may have at most one active timer. Different employees may run timers concurrently.
- Enforce the single-active-timer rule even for concurrent requests.
- Attempting a second start returns an English validation error, leaves the existing timer running and creates no new record.
- Support Start and Stop only; no pause/resume.
- Restarting a timer means starting again after stopping. Each start creates a separate time record.
- Persist the start timestamp immediately. The timer continues to accrue elapsed wall-clock time while the browser is closed or the application server is off.
- After a page reload or application restart, restore the active timer from persisted timestamps. On Stop, persist the end timestamp.
- Records cannot be edited or deleted. Manual entry of work time is not supported.

## 5. Reports

### Period and filters

- Support arbitrary dates, Today, Last 7 days and Last month.
- Last 7 days and Last month are rolling periods, not the current calendar week/month.
- Interpret report dates and day boundaries in `Asia/Aqtau`.
- Filter by one employee, multiple employees, position and department.
- Combine different filters with AND. Multiple employee selections match any selected employee.

### Results

- Show total tracked time for each matching employee.
- Show individual time-record details when exactly one employee is selected.
- Include active timers up to one common report-generation timestamp. Clearly mark running timers and show the calculation timestamp.
- Count only the intersection of a record with the requested period, including records that start before or end after it.
- An empty report must display an explicit empty state and a zero total.
- Sum exact durations first, then discard incomplete minutes from each displayed aggregate. Do not truncate each record before summing it.
- Display durations in hours and minutes, with a working-time equivalent alongside them.
- Working equivalents: 1 day = 8 hours, 1 week = 40 hours, 1 month = 160 hours.
- CSV export is outside this version.

## 6. Interface and demonstration data

- All UI text, validation errors, documentation and README content must be in English.
- Separate Employees, Timers and Reports pages with navigation.
- Responsive layout usable on mobile devices.
- No specific visual style has been requested; use a simple, readable interface.
- Initialize the first run with fictional employees and example time records.
- Initialization must be repeatable without duplicating seed data, resetting the database or overwriting existing records.
- Use Turbo for navigation, form handling and partial updates where appropriate.

## 7. Running, persistence and deployment

- Project location: `/home/taskibaev/projects/teamtime` on the server accessed via SSH alias `lab`.
- Local startup command: `docker compose up --build`.
- Local startup must include database readiness, schema migrations and first-run sample-data initialization.
- Persist local PostgreSQL data in a Docker volume across normal container restarts/recreation.
- Deploy the application container to Render and use PostgreSQL hosted by Neon.
- Supply a deployment-specific configuration and separate README instructions, including environment configuration and a migration step.
- Keep real credentials and secrets out of version control.
- The proposed environment variables are `APP_ENV`, `APP_DEBUG`, `APP_SECRET` and `DATABASE_URL`; finalize additional deployment variables during implementation.
- Keep the application timezone configured as `Asia/Aqtau`.
- Account for free-hosting sleep/restarts by persisting timer timestamps in PostgreSQL.
- Backup and restore instructions and automated backup configuration are outside the agreed scope.

## 8. Testing and quality gates

Run checks locally through Docker Compose. GitHub Actions is not required.
Use an isolated PostgreSQL test database; never run destructive test setup against the application or Neon demonstration database.
Provide a documented single command for all tests and PHPStan.

Required coverage:

1. Add and edit an employee; enforce unique first-name/last-name combinations.
2. Validate required descriptions, whitespace and the 255-character limit.
3. Start and stop a timer; verify persisted timestamps and duration.
4. Stop and start again; verify separate records.
5. Reject a second active timer, including concurrent starts.
6. Run timers independently for different employees.
7. Restore active timers after a page reload and application restart; include elapsed downtime.
8. Preserve employees and stopped records across restarts.
9. Filter reports by employees, position, department and combined filters.
10. Clip records at report boundaries and handle the configured timezone.
11. Include active timers using a consistent calculation timestamp and a visible status.
12. Return an empty report with zero total.
13. Sum exact durations before truncating minutes; calculate working equivalents.
14. Seed only once without duplicating or overwriting user data.
15. Browser checks for employee creation, start/stop, Turbo behavior and report display.

Use controllable clocks for time-dependent tests rather than waiting in real time. Include an integration check of persistence across actual application/container restarts in addition to clock-based logic tests.

## 9. Documentation and AI usage

Keep project documentation in:

```text
.docs/
  specification.md
  ai/
    tools.md
    prompts.md
    fixes.md
```

- `tools.md`: AI tools and the actual models used, with their role in the work.
- `prompts.md`: meaningful real prompts used during implementation and review.
- `fixes.md`: actual code problems found, their causes, fixes and verification.
- Do not invent model names, prompts, discovered bugs or test outcomes.
- README must explain local startup, deployment, tests, key implementation decisions, and summarize AI usage with links to the detailed files.
- Documentation is English-only.

## 10. Delivery

- Deliver readable source code, Docker configuration, database migrations, sample data, tests and documentation.
- The public repository will be on GitHub. The owner will create it later and supply the URL; do not create or publish a repository yet.
- A working online demo is the chosen deployment target, but whether publication is a mandatory first-version acceptance criterion remains open.
- TeamTime / `teamtime` is the provisional name.

## 11. Resolved implementation defaults

These are implementation defaults, not retroactive claims of stakeholder approval:

1. Position and department are required valid catalog identifiers.
2. Suggestions may populate descriptions, but descriptions remain freely editable.
3. Names are trimmed, repeated internal whitespace is collapsed, and pair uniqueness is case-insensitive after normalization.
4. Boundaries use `Asia/Aqtau`. Today is `[local midnight, next local midnight)`. Last 7 days starts six local dates before today. Last month uses calendar-month subtraction from today's local midnight and PHP's standard month-end normalization. Custom start/end dates are inclusive in the UI and implemented with an exclusive midnight after the end date.
5. Exact seconds are aggregated before incomplete minutes are discarded. Working equivalents are rounded to two decimal places.
6. Position/department filters use current employee assignments; version one stores no historical assignment snapshot.
7. Publication is not an acceptance condition until explicitly authorized. No account creation, card registration, deployment, or publication is authorized.
8. The name remains TeamTime. Runtime variables are `APP_ENV`, `APP_DEBUG`, `APP_SECRET`, and `DATABASE_URL`.
