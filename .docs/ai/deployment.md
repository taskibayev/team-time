# Deployment

## Render and Neon

- The production PostgreSQL database is hosted in Neon.
- The application is deployed as a Docker web service through the root `render.yaml` Blueprint.
- `DATABASE_URL` is supplied to Render as a secret and is not committed to Git.
- Database migrations and initial seed data are applied by `docker/entrypoint.sh` when the service starts.
- Importmap vendor assets are downloaded during the Docker build before AssetMapper compiles the production assets. The generated `assets/vendor/` directory remains excluded from Git.
- Render checks application readiness through `/timers`.
