<?php

namespace Tests\Contract;

use App\Services\Analysis\AnalysisClient;
use Tests\TestCase;

final class AnalysisServiceLiveContractTest extends TestCase
{
    public function test_laravel_client_can_call_the_live_analysis_service(): void
    {
        $summary = app(AnalysisClient::class)->syntheticSummary();

        $this->assertSame('student-wellbeing-v1', $summary->datasetId);
        $this->assertSame(5, $summary->sampleSize);
        $this->assertEqualsWithDelta(12.8, $summary->mean, 0.0001);
        $this->assertEqualsWithDelta(1.9235, $summary->sampleStandardDeviation, 0.0001);
    }
}
