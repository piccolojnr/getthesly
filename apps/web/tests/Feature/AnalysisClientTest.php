<?php

namespace Tests\Feature;

use App\Services\Analysis\AnalysisClient;
use App\Services\Analysis\AnalysisServiceException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class AnalysisClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('analysis.url', 'http://analysis.test');
        config()->set('analysis.token', 'test-shared-token');
    }

    public function test_client_sends_authenticated_versioned_request_and_parses_contract(): void
    {
        Http::fake(['analysis.test/*' => Http::response([
            'contract_version' => '1.0',
            'dataset_id' => 'student-wellbeing-v1',
            'summary' => ['sample_size' => 5, 'mean' => 12.8, 'median' => 13.0, 'sample_standard_deviation' => 1.9235],
        ])]);

        $summary = app(AnalysisClient::class)->syntheticSummary();

        $this->assertSame(5, $summary->sampleSize);
        $this->assertEqualsWithDelta(12.8, $summary->mean, 0.0001);
        Http::assertSent(fn ($request): bool => $request->url() === 'http://analysis.test/api/v1/analyze/synthetic-summary'
            && $request->hasHeader('Authorization', 'Bearer test-shared-token')
            && $request['dataset_id'] === 'student-wellbeing-v1');
    }

    public function test_client_fails_closed_when_authentication_is_missing(): void
    {
        config()->set('analysis.token', '');

        $this->expectException(AnalysisServiceException::class);
        app(AnalysisClient::class)->syntheticSummary();
    }

    public function test_client_rejects_invalid_service_response_shapes(): void
    {
        Http::fake(['analysis.test/*' => Http::response(['unexpected' => 'shape'])]);

        $this->expectException(AnalysisServiceException::class);
        app(AnalysisClient::class)->syntheticSummary();
    }
}
