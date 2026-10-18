<?php

return [
    'provider' => env('RESEARCH_AI_PROVIDER', 'openai'),
    'model' => env('RESEARCH_AI_MODEL', 'gpt-4.1-mini'),
    'timeout_seconds' => (int) env('RESEARCH_AI_TIMEOUT_SECONDS', 20),
    'rate_limit_attempts' => (int) env('RESEARCH_AI_RATE_LIMIT_ATTEMPTS', 5),
    'rate_limit_ip_attempts' => (int) env('RESEARCH_AI_IP_RATE_LIMIT_ATTEMPTS', 20),
    'rate_limit_decay_seconds' => (int) env('RESEARCH_AI_RATE_LIMIT_DECAY_SECONDS', 600),
    'draft_retention_days' => (int) env('RESEARCH_DRAFT_RETENTION_DAYS', 30),
];
