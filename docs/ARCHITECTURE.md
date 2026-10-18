# Architecture

## Services

`apps/web` is a Laravel 13 application using Livewire and Blade. It owns web requests, session state, research drafts, and queues. PostgreSQL is the target database; database sessions allow an anonymous draft to be recovered by the same browser session.

`services/analysis` is a separately deployable FastAPI process. HTTP schemas and routes live in `analysis_api`; statistical primitives live in `analysis_engine`, which has no FastAPI dependency. Laravel calls it through `App\Services\Analysis\AnalysisClient` using a versioned contract and bearer authentication.

## Research understanding

The `UnderstandResearch` Laravel AI SDK agent has no tools or conversation history. It receives a title and question encoded as untrusted JSON, and is instructed to restate only what the student provided, identify directly relevant concepts, ask at most one essential clarification, and never select a statistical test or claim analysis was performed. The SDK requests structured output. `ResearchInterpretation::fromArray` then strictly checks the exact keys, types, list shape, item counts, and field lengths before any output is saved or rendered.

Provider, model, timeout, independent per-session and per-IP rate limits, and retention are configured in `config/research.php`. Provider credentials such as `OPENAI_API_KEY` remain server-side. Draft revision fields and the session payload are encrypted at rest with Laravel's `APP_KEY`; keep that key stable and backed up for the intended retention period. Student descriptions are sent to the configured AI provider for interpretation. Provider errors and invalid responses are caught at the Livewire boundary; the student sees a generic retry message and raw input or exception details are not logged.

The structured response has exactly these fields: `summary` (1–500 characters), `objective` (1–500 characters), `concepts` (a list of up to eight strings, each 1–100 characters), and `clarification` (null or a 1–500-character question). Unknown fields, missing fields, malformed lists, blank text, and overlong values are rejected.

## Draft lifecycle and access

The title is limited to 120 characters and the research question to 20–2,000 characters. A valid first submission creates an anonymous draft with a UUID and a random session capability. Only a SHA-256 hash of that capability is stored. Draft lookup requires the UUID and matching capability from the current server-side session; sequential database IDs are never used as access credentials or placed in URLs. Database sessions retain the student's authorization for 30 days, matching the default draft inactivity cleanup period.

Every submission, edit, interpretation, failure, and confirmation creates a numbered `research_draft_revisions` row. Previous revisions remain unchanged. Editing adds a draft revision with no interpretation or confirmation, so a stale interpretation cannot be approved. If the AI asks for clarification, the student must edit the description before confirmation is available. Confirming creates a new revision with `confirmed_at`; the same session restores the saved inputs and ready state. A new session cannot load another session's draft. Authorized visits refresh `last_active_at`, which controls inactivity cleanup.

Drafts with no authorized activity for 30 days are deleted with their revisions by `research:drafts:prune`, scheduled daily. Configure the Laravel scheduler in each production environment and adjust `RESEARCH_DRAFT_RETENTION_DAYS` if retention policy changes. This is a temporary anonymous-session boundary, not a replacement for future accounts or a formal research data retention policy.

## Decisions and deferred work

- Keep the web and Python services independently deployable in this repository.
- Keep calculations framework-independent and limited to the deterministic service fixture.
- Do not accept executable code or arbitrary analysis plans.
- Dataset uploads, full statistical analysis, Chapter Four drafting, accounts, and billing remain deferred.
