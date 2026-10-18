# Analysis Service

Private FastAPI service with a framework-independent calculation package. Install from this directory with `pip install -e '.[dev]'`. Run locally using `python -m analysis_api` after setting `ANALYSIS_SERVICE_TOKEN` from `.env.example`. For reload during development, use `uvicorn analysis_api.main:app --reload --port 8100`.

Health is available at `GET /api/v1/health`. The only calculation endpoint is `POST /api/v1/analyze/synthetic-summary`, requires bearer auth, and accepts only `student-wellbeing-v1`. Interactive API documentation is disabled because this service is private. See `../../contracts/analysis-v1.openapi.yaml` for the versioned contract.
