<?php

namespace App\Providers;

use App\Services\Research\LaravelAiResearchInterpreter;
use App\Services\Research\ResearchInterpreter;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ResearchInterpreter::class, LaravelAiResearchInterpreter::class);
    }
}
