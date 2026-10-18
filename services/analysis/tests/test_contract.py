from pathlib import Path

import yaml

from analysis_api.schemas import SyntheticSummaryResponse

CONTRACT = Path(__file__).parents[3] / "contracts" / "analysis-v1.openapi.yaml"


def test_python_summary_schema_matches_shared_openapi_contract() -> None:
    document = yaml.safe_load(CONTRACT.read_text(encoding="utf-8"))
    schema = document["components"]["schemas"]["SyntheticSummary"]
    generated = SyntheticSummaryResponse.model_json_schema()

    assert schema["required"] == generated["required"]
    assert (
        schema["properties"]["contract_version"]["const"]
        == generated["properties"]["contract_version"]["const"]
    )
    assert (
        schema["properties"]["summary"]["required"]
        == generated["$defs"]["SummaryStatistics"]["required"]
    )
    assert set(schema["properties"]["summary"]["properties"]) == set(
        generated["$defs"]["SummaryStatistics"]["properties"]
    )
