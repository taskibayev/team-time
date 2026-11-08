# AI tools

- Codex, using the GPT-5 model exposed by the workspace, inspected the specification, implemented the application, maintained documentation, and invoked Docker checks.
- Shell and Docker Compose installed dependencies and ran the application quality gates.
- PHP CS Fixer enforces the Symfony coding standard. `composer cs:fix` formats PHP files and `composer cs:check` performs a non-mutating CI-style check.
- PHP_CodeSniffer applies PSR-12 as the strict second stage of both commands, catching layout violations that PHP CS Fixer does not report.
- A project-specific PHP_CodeSniffer rule requires a blank line between a closing brace and the next standalone statement. It rejects and fixes cases such as `}if`, `}$value`, and a statement placed immediately on the following line, which neither the stock Symfony fixer set nor PSR-12 detects.
- PHPUnit includes unit, PostgreSQL-backed integration, and HTTP functional test suites. Test-created employees and records use unique identifiers and are removed after each database-backed test.

- GitHub is the source repository for the Render deployment. Render builds the project Dockerfile, while Neon provides production PostgreSQL; credentials remain dashboard-managed environment variables.
