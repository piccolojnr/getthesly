"""Strict request and response models for the versioned API."""

from typing import Literal

from pydantic import BaseModel, ConfigDict, Field


class StrictModel(BaseModel):
    model_config = ConfigDict(extra="forbid", strict=True)


class HealthResponse(StrictModel):
    status: Literal["ok"]
    service: Literal["analysis"]


class SyntheticSummaryRequest(StrictModel):
    dataset_id: Literal["student-wellbeing-v1"]


class SummaryStatistics(StrictModel):
    sample_size: int = Field(ge=2)
    mean: float
    median: float
    sample_standard_deviation: float = Field(ge=0)


class SyntheticSummaryResponse(StrictModel):
    contract_version: Literal["1.0"]
    dataset_id: Literal["student-wellbeing-v1"]
    summary: SummaryStatistics


class ErrorDetail(StrictModel):
    code: str
    message: str


class ErrorResponse(StrictModel):
    error: ErrorDetail
