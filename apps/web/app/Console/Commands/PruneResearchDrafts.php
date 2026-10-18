<?php

namespace App\Console\Commands;

use App\Models\ResearchDraft;
use Illuminate\Console\Command;

final class PruneResearchDrafts extends Command
{
    protected $signature = 'research:drafts:prune {--dry-run : Report eligible drafts without deleting them}';

    protected $description = 'Delete research drafts that have passed their retention period';

    public function handle(): int
    {
        $cutoff = now()->subDays(max(1, (int) config('research.draft_retention_days', 30)));
        $drafts = ResearchDraft::query()->where('last_active_at', '<', $cutoff);
        $count = $drafts->count();

        if (! $this->option('dry-run')) {
            $drafts->delete();
        }

        $this->components->info(($this->option('dry-run') ? 'Eligible' : 'Deleted').' drafts: '.$count);

        return self::SUCCESS;
    }
}
