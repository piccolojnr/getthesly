# getthesly

An early foundation for a student-friendly statistical analysis product. Milestone 1 includes a polished research-description screen, a protected versioned FastAPI smoke-analysis endpoint, and the boundaries for future analysis work. Research interpretation and dataset upload are not connected yet.

## Repository

- `apps/web/` — Laravel 13, Livewire, Blade, and Tailwind web application.
- `services/analysis/` — FastAPI service and framework-independent statistical engine.
- `contracts/` — versioned HTTP API contract shared by both services.
- `docs/` — architecture, design, scope, skill notes, and deployment setup.

## Requirements

PHP 8.3+, Composer 2, Node 20+, Python 3.11+, uv 0.13+, PostgreSQL 15+.

## Run locally

1. In `apps/web`, run `composer install`, `npm ci`, and copy `.env.example` to `.env` (`cp .env.example .env`, or `Copy-Item .env.example .env` in PowerShell).
2. Create a local PostgreSQL database with `createdb getthesly`. Copy `.env.example` to `.env`, set `DB_*` for PostgreSQL, and use the same strong `ANALYSIS_SERVICE_TOKEN` in both services. Set `ANALYSIS_SERVICE_URL=http://127.0.0.1:8100` in the web environment.
3. Run `php artisan key:generate`, `php artisan migrate`, and `php artisan serve` in `apps/web`. In a second terminal, run `npm run dev` there.
4. Install [uv](https://docs.astral.sh/uv/) if it is not available. In `services/analysis`, run `uv sync --locked --extra dev`. Copy `.env.example` to `.env` and set the same token as Laravel. Start the service with `uv run --locked uvicorn analysis_api.main:app --reload --port 8100`.

The landing form is a local preview and clearly reports that AI interpretation is not connected. The API smoke endpoint accepts only its fixed synthetic dataset; it does not execute user-supplied code or analyze uploads.

## Verify

```powershell
Set-Location apps/web
composer test
vendor/bin/pint --test
npm run build

Set-Location ../../services/analysis
uv run --locked pytest
uv run --locked ruff check .
uv run --locked ruff format --check .
```

CI runs PHPUnit, Pint, frontend builds, Python lint/tests, and a service-to-service contract smoke check. See [deployment guidance](docs/DEPLOYMENT.md) before configuring hosted environments.
