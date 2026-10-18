# Analysis Service

Private FastAPI service with a framework-independent calculation package. Install uv, then run `uv sync --locked --extra dev` from this directory. Set `ANALYSIS_SERVICE_TOKEN` from `.env.example`. Start locally with `uv run --locked uvicorn analysis_api.main:app --reload --port 8100`.

Health is available at `GET /api/v1/health`. The only calculation endpoint is `POST /api/v1/analyze/synthetic-summary`, requires bearer auth, and accepts only `student-wellbeing-v1`. Interactive API documentation is disabled because this service is private. See `../../contracts/analysis-v1.openapi.yaml` for the versioned contract.
