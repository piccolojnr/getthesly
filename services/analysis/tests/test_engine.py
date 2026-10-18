from analysis_engine import summarize_student_wellbeing


def test_synthetic_summary_is_deterministic_and_uses_sample_standard_deviation() -> None:
    expected = {
        "sample_size": 5,
        "mean": 12.8,
        "median": 13.0,
        "sample_standard_deviation": 1.9235,
    }

    assert summarize_student_wellbeing() == expected
    assert summarize_student_wellbeing() == expected
