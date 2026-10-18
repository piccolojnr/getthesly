# Architecture

## Services

`apps/web` is a Laravel 13 application using Livewire for the form interaction and Blade for rendering. It owns web requests, session UI state, persistence, and queues. PostgreSQL is the target database; database-backed queues are configured for the first deployment.

The official Laravel AI SDK (`laravel/ai`, Composer constraint `^1.2.3`) is included for future use. No model calls, provider credentials, or research agents are configured in this milestone.

`services/analysis` is a separately deployable FastAPI process. HTTP schemas and routing live under `analysis_api`; statistical primitives live in `analysis_engine`, which has no FastAPI dependency. The only calculation in this milestone is a deterministic summary over fixed synthetic observations.

Laravel calls the analysis service through `App\Services\Analysis\AnalysisClient`. The base URL, bearer token, and timeouts are environment-configured. The service rejects calls without a configured matching token. Public health checks disclose only process status. `/api/v1` is versioned; `contracts/analysis-v1.openapi.yaml` defines the shared request and response shapes.

## Data and failure boundaries

Research descriptions are not persisted in this milestone. Laravel validates service responses and converts transport, status, and shape failures into a domain exception; logs avoid request bodies and tokens. Future uploads must use private object storage and short-lived access, not local ephemeral application disks. Analysis should run in queues once jobs become long-running.

## Decisions

- Keep the web and Python services in separate deployable roots in one repository.
- Keep calculations framework-independent for reuse and direct unit testing.
- Use a fixed demo dataset for integration checks; do not accept executable expressions or arbitrary analysis plans.
- Defer authentication, uploads, AI agents, and full analysis until their data and security boundaries are designed.
