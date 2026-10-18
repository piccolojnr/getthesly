"""Environment-backed service configuration."""

from functools import lru_cache

from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    analysis_service_token: str | None = None
    analysis_host: str = "127.0.0.1"
    analysis_port: int = 8100

    model_config = SettingsConfigDict(env_file=".env", extra="ignore", case_sensitive=False)


@lru_cache
def get_settings() -> Settings:
    """Read settings once per process; tests can clear this cache."""
    return Settings()
