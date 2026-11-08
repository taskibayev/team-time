# TeamTime

TeamTime is a Symfony 7.4/PostgreSQL employee time tracker. All bundled data is fictional.

## Local use

Run `docker compose up --build`, then open <http://localhost:8080>. Startup waits for PostgreSQL, runs migrations, and idempotently adds sample data. The named volume preserves data.

## Quality checks

Run `docker compose --profile test run --rm test`. This uses an isolated database and runs migrations, PHPUnit, and PHPStan at level max. To check real restart persistence, create a record, run `docker compose restart app`, and confirm it remains visible.

## Deployment

The repository contains a Render Blueprint in `render.yaml`. To deploy it:

1. Create a Neon PostgreSQL database and copy its connection string. Keep `sslmode=require` in the URL.
2. In Render, create a new Blueprint from this GitHub repository.
3. When prompted for `DATABASE_URL`, paste the Neon connection string. Do not commit it.
4. Render generates `APP_SECRET`, builds the Docker image, runs migrations and idempotently seeds fictional sample data.
5. Confirm that the Render health check for `/timers` is green.

The container listens on Render's `PORT` and defaults to port `8080` locally. Production uses `APP_ENV=prod` and `APP_DEBUG=0`. Every commit to `main` triggers a new deployment.

Implementation defaults are in [the specification](.docs/specification.md). AI records: [tools](.docs/ai/tools.md), [prompts](.docs/ai/prompts.md), and [fixes](.docs/ai/fixes.md).
