# Deployment

Deployment is prepared but not verified. No Laravel Cloud account, application, service credentials, or remote environment was available during this setup.

## Laravel Cloud: web application

Create a Cloud application from the repository and select `apps/web` when Cloud detects the monorepo. Laravel Cloud treats each selected application as an independent resource while making sibling directories available during builds. Confirm the root and build logs in the dashboard before deploying; this repository has not been deployed.

Build commands from the web root:

```sh
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Configure `php artisan migrate --force` as the deploy command so migrations run against the selected environment's database before traffic changes.

Serve with Cloud's standard Laravel runtime. Provision PostgreSQL and set `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`. Set `APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `SESSION_DRIVER=database`, `SESSION_LIFETIME=43200`, `SESSION_ENCRYPT=true`, and `CACHE_STORE=database`; migrations create the session and shared cache tables. Keep `APP_KEY` stable and backed up because it encrypts research revisions and session payloads. Configure `RESEARCH_AI_PROVIDER`, `RESEARCH_AI_MODEL`, `RESEARCH_AI_TIMEOUT_SECONDS`, the rate-limit values, `RESEARCH_DRAFT_RETENTION_DAYS`, and the chosen provider credential (for example, `OPENAI_API_KEY`). Keep credentials in Cloud secrets and never in frontend variables. Continue to set `ANALYSIS_SERVICE_URL`, `ANALYSIS_SERVICE_TOKEN`, and both analysis timeout values. Add a database queue worker running `php artisan queue:work database --tries=3` when asynchronous jobs are introduced; the database queue migration is included. Run Laravel's scheduler every minute with `php artisan schedule:run` so abandoned research drafts are removed daily. Laravel Cloud can provide separate worker processes and managed database resources; confirm current plan and environment options in its dashboard.

Use private S3-compatible object storage for future uploaded datasets. Do not store them on the app container's local disk.

## Python service

Create a second Laravel Cloud application from the same repository and select `services/analysis`. Cloud documents first-class monorepo deployments and Python support, including FastAPI; its current Python runtime version support is listed as TBD, so confirm the available version and process settings in the dashboard. Use the included `Dockerfile` or configure the Python runtime with these commands from the service root:

```sh
pip install .
python -m analysis_api
```

Set `ANALYSIS_HOST=0.0.0.0`, `ANALYSIS_SERVICE_TOKEN` to the same high-entropy secret in both environments, and let the service use the hosting-provided `PORT` (it defaults to `ANALYSIS_PORT`). Set Laravel's URL to the analysis application's private or otherwise access-controlled address. Restrict inbound traffic at the hosting network layer to Laravel where possible; retain bearer authentication and TLS.

Required Python settings: `ANALYSIS_SERVICE_TOKEN`, `ANALYSIS_HOST`, `ANALYSIS_PORT`. Do not expose the service publicly without network restrictions and a secret rotation plan. The included Dockerfile can also be used with another container host if the Cloud Python runtime does not fit the workload.

## Readiness limits

The Laravel Cloud guide describes selecting each app directory from a monorepo, independent resources, managed PostgreSQL and queues, S3-compatible storage, and build/deploy commands. See [Laravel Cloud monorepo documentation](https://laravel.com/cloud/docs/monorepos) and [Cloud deployment guide](https://laravel.com/cloud/deploy-a-laravel-app). Validate Python runtime availability, root-directory configuration, build execution, migrations, private service networking, queue execution, and object storage in a non-production environment before treating deployment as ready.
