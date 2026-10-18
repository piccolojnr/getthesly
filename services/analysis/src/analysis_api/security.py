"""Private-service bearer authentication."""

import hmac
from typing import Annotated

from fastapi import Depends, Header, HTTPException, status

from analysis_api.config import Settings, get_settings


def require_analysis_token(
    settings: Annotated[Settings, Depends(get_settings)],
    authorization: Annotated[str | None, Header()] = None,
) -> None:
    """Fail closed unless the caller presents the configured shared token."""
    expected_token = settings.analysis_service_token
    if not expected_token:
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail={
                "code": "service_not_configured",
                "message": "Service authentication is not configured.",
            },
        )

    scheme, separator, supplied_token = (authorization or "").partition(" ")
    if scheme.lower() != "bearer" or not separator or not supplied_token.isascii():
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail={"code": "unauthorized", "message": "A valid service token is required."},
            headers={"WWW-Authenticate": "Bearer"},
        )

    if not hmac.compare_digest(supplied_token, expected_token):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail={"code": "unauthorized", "message": "A valid service token is required."},
            headers={"WWW-Authenticate": "Bearer"},
        )
