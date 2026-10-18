# Milestone 2 Scope

## Included

- Laravel, Livewire, Tailwind, PostgreSQL, and queue foundations.
- A responsive research-description → AI interpretation → student confirmation flow using the Laravel AI SDK.
- Anonymous research drafts with session-capability access, append-only revisions, persisted interpretation and confirmation, and 30-day inactivity cleanup.
- Server-side structured AI output validation, safe retry behavior, prompt-injection boundaries, provider timeouts, and per-session/IP rate limiting.
- A separately runnable FastAPI service with authenticated, versioned health and fixed synthetic-summary endpoints.
- A deterministic, framework-independent statistical primitive and contract checks.
- Local setup notes, CI, and Laravel Cloud deployment guidance.

## Deferred

- CSV/Excel uploads, dataset storage, validation, and privacy controls (Milestone 3).
- Statistical test selection, the full analysis engine, tables, charts, and interpretations.
- Chapter Four drafting and result downloads.
- Authentication, user workspaces, billing, and production deployment.

The Python smoke endpoint remains an integration fixture, not a student-facing analysis feature. Research AI only restates student-provided intent and does not choose tests or analyze data.
