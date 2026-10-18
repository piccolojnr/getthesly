# getthesly

An early foundation for a student-friendly statistical analysis product. The current workflow lets a student describe their study, review a plain-English AI interpretation, and confirm it. Dataset uploads and statistical analysis are not available yet.

## Repository

- `apps/web/` — Laravel 13, Livewire, Blade, and Tailwind web application.
- `services/analysis/` — FastAPI service and framework-independent statistical engine.
- `contracts/` — versioned HTTP API contract shared by both services.
- `docs/` — architecture, design, scope, skill notes, and deployment setup.

## Requirements

PHP 8.3+, Composer 2, Node 20+, Python 3.11+, uv 0.13+, PostgreSQL 15+.

## Run locally

1. In `apps/web`, run `composer install`, `npm ci`, and copy `.env.example` to `.env` (`cp .env.example .env`, or `Copy-Item .env.example .env` in PowerShell).
2. Create a local PostgreSQL database with `createdb getthesly`. Configure `DB_*`, `SESSION_DRIVER=database`, and `OPENAI_API_KEY` in `.env`. Set the provider and model with `RESEARCH_AI_PROVIDER` and `RESEARCH_AI_MODEL` if needed. The default provider is OpenAI; interpretation runs only on the server.
3. Run `php artisan key:generate`, `php artisan migrate`, and `php artisan serve` in `apps/web`. In a second terminal, run `npm run dev` there.
4. Install [uv](https://docs.astral.sh/uv/) if it is not available. In `services/analysis`, run `uv sync --locked --extra dev`. Copy `.env.example` to `.env` and set the same token as Laravel. Start the service with `uv run --locked uvicorn analysis_api.main:app --reload --port 8100`.

The web app uses database sessions and shared database cache so anonymous drafts can be recovered by the same browser session and AI calls are rate-limited across app instances. Student input is kept for up to 30 days after last activity. Set `RESEARCH_DRAFT_RETENTION_DAYS` and run Laravel's scheduler to clean up abandoned drafts. The FastAPI smoke endpoint remains an integration fixture; it does not accept user code or analyze uploads.

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
