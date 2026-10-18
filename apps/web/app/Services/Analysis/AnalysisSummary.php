<?php

namespace App\Services\Analysis;

use InvalidArgumentException;

final readonly class AnalysisSummary
{
    public function __construct(
        public string $datasetId,
        public int $sampleSize,
        public float $mean,
        public float $median,
        public float $sampleStandardDeviation,
    ) {
    }

    public static function fromPayload(mixed $payload): self
    {
        if (!is_array($payload)
            || array_diff(array_keys($payload), ['contract_version', 'dataset_id', 'summary']) !== []
            || array_diff(['contract_version', 'dataset_id', 'summary'], array_keys($payload)) !== []
            || ($payload['contract_version'] ?? null) !== '1.0'
            || ($payload['dataset_id'] ?? null) !== 'student-wellbeing-v1') {
            throw new InvalidArgumentException('Invalid analysis service response.');
        }

        $summary = $payload['summary'] ?? null;

        if (!is_array($summary)
            || array_diff(array_keys($summary), ['sample_size', 'mean', 'median', 'sample_standard_deviation']) !== []
            || array_diff(['sample_size', 'mean', 'median', 'sample_standard_deviation'], array_keys($summary)) !== []
            || !is_string($payload['dataset_id'] ?? null)
            || !is_int($summary['sample_size'] ?? null)
            || $summary['sample_size'] < 2
            || !self::isFiniteNumber($summary['mean'] ?? null)
            || !self::isFiniteNumber($summary['median'] ?? null)
            || !self::isFiniteNumber($summary['sample_standard_deviation'] ?? null)
            || $summary['sample_standard_deviation'] < 0) {
            throw new InvalidArgumentException('Invalid analysis service response.');
        }

        return new self(
            datasetId: $payload['dataset_id'],
            sampleSize: $summary['sample_size'],
            mean: (float) $summary['mean'],
            median: (float) $summary['median'],
            sampleStandardDeviation: (float) $summary['sample_standard_deviation'],
        );
    }

    private static function isFiniteNumber(mixed $value): bool
    {
        return (is_int($value) || is_float($value)) && is_finite((float) $value);
    }
}
