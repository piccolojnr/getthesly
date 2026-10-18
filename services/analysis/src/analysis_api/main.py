"""FastAPI routing and structured error responses."""

import logging
from typing import Any

from fastapi import Depends, FastAPI, HTTPException, Request
from fastapi.exceptions import RequestValidationError
from fastapi.responses import JSONResponse

from analysis_api.schemas import (
    ErrorResponse,
    HealthResponse,
    SummaryStatistics,
    SyntheticSummaryRequest,
    SyntheticSummaryResponse,
)
from analysis_api.security import require_analysis_token
from analysis_engine import summarize_student_wellbeing

app = FastAPI(
    title="getthesly Analysis Service",
    version="1.0.0",
    docs_url=None,
    redoc_url=None,
    openapi_url=None,
)
logger = logging.getLogger("getthesly.analysis")


@app.exception_handler(HTTPException)
async def http_error_handler(request: Request, exc: HTTPException) -> JSONResponse:
    detail: Any = exc.detail
    if isinstance(detail, dict) and "code" in detail and "message" in detail:
        error = detail
    else:
        error = {"code": "http_error", "message": "The request could not be completed."}
    return JSONResponse(
        status_code=exc.status_code,
        content=ErrorResponse(error=error).model_dump(),
        headers=exc.headers,
    )


@app.exception_handler(RequestValidationError)
async def validation_error_handler(request: Request, exc: RequestValidationError) -> JSONResponse:
    return JSONResponse(
        status_code=422,
        content=ErrorResponse(
            error={
                "code": "invalid_request",
                "message": "The request does not match the API contract.",
            }
        ).model_dump(),
    )


@app.exception_handler(Exception)
async def unexpected_error_handler(request: Request, exc: Exception) -> JSONResponse:
    logger.error(
        "Unhandled analysis service error",
        exc_info=(type(exc), exc, exc.__traceback__),
    )
    return JSONResponse(
        status_code=500,
        content=ErrorResponse(
            error={"code": "internal_error", "message": "An unexpected service error occurred."}
        ).model_dump(),
    )


@app.get("/api/v1/health", response_model=HealthResponse, tags=["health"])
def health() -> HealthResponse:
    return HealthResponse(status="ok", service="analysis")


@app.post(
    "/api/v1/analyze/synthetic-summary",
    response_model=SyntheticSummaryResponse,
    responses={
        401: {"model": ErrorResponse},
        422: {"model": ErrorResponse},
        503: {"model": ErrorResponse},
    },
    dependencies=[Depends(require_analysis_token)],
    tags=["analysis"],
)
def synthetic_summary(request: SyntheticSummaryRequest) -> SyntheticSummaryResponse:
    summary = summarize_student_wellbeing()

    return SyntheticSummaryResponse(
        contract_version="1.0",
        dataset_id=request.dataset_id,
        summary=SummaryStatistics(**summary),
    )
