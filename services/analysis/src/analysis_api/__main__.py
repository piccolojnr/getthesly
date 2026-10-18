"""Run the ASGI server using environment-backed host and port settings."""

import os

import uvicorn

from analysis_api.config import get_settings


def main() -> None:
    settings = get_settings()
    port = int(os.environ.get("PORT", settings.analysis_port))
    uvicorn.run("analysis_api.main:app", host=settings.analysis_host, port=port)


if __name__ == "__main__":
    main()
