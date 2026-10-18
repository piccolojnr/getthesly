"""Small deterministic calculation used to exercise the private service contract."""

from statistics import mean, median, stdev

STUDENT_WELLBEING_SAMPLE: tuple[float, ...] = (10.0, 12.0, 13.0, 14.0, 15.0)


def summarize_student_wellbeing() -> dict[str, int | float]:
    """Return descriptive statistics for the fixed synthetic sample."""
    values = STUDENT_WELLBEING_SAMPLE

    return {
        "sample_size": len(values),
        "mean": round(mean(values), 4),
        "median": round(median(values), 4),
        "sample_standard_deviation": round(stdev(values), 4),
    }
