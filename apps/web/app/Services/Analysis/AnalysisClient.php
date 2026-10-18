<?php

namespace App\Services\Analysis;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

final class AnalysisClient
{
    public function syntheticSummary(): AnalysisSummary
    {
        $baseUrl = rtrim((string) config('analysis.url'), '/');
        $token = (string) config('analysis.token', '');

        if ($baseUrl === '' || $token === '') {
            throw new AnalysisServiceException('The analysis service is not configured.');
        }

        try {
            $response = Http::baseUrl($baseUrl)
                ->withToken($token)
                ->acceptJson()
                ->connectTimeout(max(1, (int) config('analysis.connect_timeout', 2)))
                ->timeout(max(1, (int) config('analysis.timeout', 8)))
                ->post('/api/v1/analyze/synthetic-summary', ['dataset_id' => 'student-wellbeing-v1']);
        } catch (ConnectionException $exception) {
            Log::warning('Analysis service connection failed.');
            throw new AnalysisServiceException('The analysis service could not be reached.', previous: $exception);
        }

        if ($response->failed()) {
            Log::warning('Analysis service returned an unsuccessful status.', ['status' => $response->status()]);
            throw new AnalysisServiceException('The analysis service returned an error.');
        }

        try {
            return AnalysisSummary::fromPayload($response->json());
        } catch (InvalidArgumentException $exception) {
            Log::warning('Analysis service returned an invalid response.');
            throw new AnalysisServiceException('The analysis service returned an invalid response.', previous: $exception);
        }
    }
}
