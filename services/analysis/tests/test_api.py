from fastapi.testclient import TestClient

from analysis_api.config import Settings, get_settings
from analysis_api.main import app


def client_with_token(token: str | None) -> TestClient:
    app.dependency_overrides[get_settings] = lambda: Settings(analysis_service_token=token)
    return TestClient(app)


def teardown_function() -> None:
    app.dependency_overrides.clear()


def test_health_is_available_without_private_service_credentials() -> None:
    response = TestClient(app).get("/api/v1/health")

    assert response.status_code == 200
    assert response.json() == {"status": "ok", "service": "analysis"}


def test_analysis_endpoint_returns_contract_for_fixed_dataset() -> None:
    response = client_with_token("local-test-secret").post(
        "/api/v1/analyze/synthetic-summary",
        headers={"Authorization": "Bearer local-test-secret"},
        json={"dataset_id": "student-wellbeing-v1"},
    )

    assert response.status_code == 200
    assert response.json() == {
        "contract_version": "1.0",
        "dataset_id": "student-wellbeing-v1",
        "summary": {
            "sample_size": 5,
            "mean": 12.8,
            "median": 13.0,
            "sample_standard_deviation": 1.9235,
        },
    }


def test_analysis_endpoint_rejects_missing_or_wrong_token() -> None:
    missing = client_with_token("local-test-secret").post(
        "/api/v1/analyze/synthetic-summary", json={"dataset_id": "student-wellbeing-v1"}
    )
    wrong = client_with_token("local-test-secret").post(
        "/api/v1/analyze/synthetic-summary",
        headers={"Authorization": "Bearer wrong-token"},
        json={"dataset_id": "student-wellbeing-v1"},
    )

    assert missing.status_code == wrong.status_code == 401
    assert missing.json()["error"]["code"] == "unauthorized"


def test_analysis_endpoint_rejects_arbitrary_dataset_and_extra_fields() -> None:
    client = client_with_token("local-test-secret")
    headers = {"Authorization": "Bearer local-test-secret"}

    unknown_dataset = client.post(
        "/api/v1/analyze/synthetic-summary",
        headers=headers,
        json={"dataset_id": "other", "expression": "__import__('os').system('id')"},
    )
    extra_key = client.post(
        "/api/v1/analyze/synthetic-summary",
        headers=headers,
        json={"dataset_id": "student-wellbeing-v1", "code": "print(1)"},
    )

    assert unknown_dataset.status_code == extra_key.status_code == 422
    assert unknown_dataset.json()["error"]["code"] == "invalid_request"


def test_analysis_endpoint_fails_closed_without_configured_token() -> None:
    response = client_with_token(None).post(
        "/api/v1/analyze/synthetic-summary",
        headers={"Authorization": "Bearer arbitrary-token"},
        json={"dataset_id": "student-wellbeing-v1"},
    )

    assert response.status_code == 503
    assert response.json()["error"]["code"] == "service_not_configured"
