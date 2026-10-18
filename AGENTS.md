# Repository Guidelines

## Project Structure

This repository keeps the web application in `apps/web`, the Python analysis API in `services/analysis`, shared versioned API contracts in `contracts`, and architecture, design, scope, and deployment notes in `docs`. GitHub Actions workflows live in `.github/workflows`. Keep statistical calculations in the Python `engine` package so they can be called without FastAPI.

## Local Development

Install PHP dependencies with `composer install` in `apps/web`, then frontend dependencies with `npm install` there. Copy `.env.example` to `.env`, set a PostgreSQL database and the shared `ANALYSIS_SERVICE_TOKEN`, run `php artisan key:generate` and `php artisan migrate`, then start the web server with `php artisan serve` and Vite with `npm run dev`.

For Python, create a virtual environment in `services/analysis`, run `pip install -e '.[dev]'`, then start `uvicorn analysis_api.main:app --reload --port 8000`. See the root README for test and build commands.

## Style and Naming

Use PSR-12 PHP formatting; run `vendor/bin/pint --test` in `apps/web`. Name PHP classes in `StudlyCase`, methods and variables in `camelCase`, and migrations with Laravel's timestamp naming. Python uses 4 spaces, type hints, `snake_case` modules and functions, and Ruff formatting/linting. Blade views use kebab-case filenames. Keep CSS tokens in `resources/css/app.css` and favor semantic, keyboard-accessible HTML.

## Tests and Contracts

Add Laravel feature tests under `apps/web/tests/Feature` and unit tests under `apps/web/tests/Unit`; run `php artisan test`. Python tests live under `services/analysis/tests` and run with `pytest`. Changes to request/response behavior must update `contracts/analysis-v1.openapi.yaml` and the matching tests. Do not claim a check passed unless it ran successfully.

## Commits and Pull Requests

Use short imperative commit subjects (for example, `Add protected analysis health check`). Describe behavior and verification in pull requests, link an issue when one exists, and include a screenshot for visible UI changes. Keep credentials out of source control; use local environment files and deployment secrets.

## Agent Instructions

Read `docs/ARCHITECTURE.md`, `docs/FRONTEND_DESIGN.md`, and `docs/MVP_SCOPE.md` before changing system boundaries or the landing screen. Preserve the milestone scope: no upload, research-AI, full-analysis, Chapter Four, account, or billing features without a new request.
