<?php

namespace App\Livewire;

use App\Models\ResearchDraft;
use App\Models\ResearchDraftRevision;
use App\Services\Research\ResearchInterpretation;
use App\Services\Research\ResearchInterpreter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

final class ResearchDescriptionForm extends Component
{
    #[Validate('required|string|max:120')]
    public string $projectTitle = '';

    #[Validate('required|string|min:20|max:2000')]
    public string $researchQuestion = '';

    #[Locked]
    public bool $editing = false;

    #[Locked]
    public ?string $errorMessage = null;

    public function mount(): void
    {
        $revision = $this->currentRevision();

        if ($revision !== null) {
            $this->projectTitle = $revision->project_title;
            $this->researchQuestion = $revision->research_question;
        }
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['projectTitle', 'researchQuestion'], true)) {
            $this->invalidateInterpretationWhenChanged($property);
        }
    }

    public function understand(ResearchInterpreter $interpreter): void
    {
        $this->validate();

        $draft = $this->draftForCurrentSession() ?? $this->createDraft();
        $rateLimitKey = 'research-understanding:'.hash('sha256', request()->ip().'|'.session()->getId());

        if (RateLimiter::tooManyAttempts($rateLimitKey, (int) config('research.rate_limit_attempts', 5))) {
            $this->errorMessage = 'Please wait a few minutes before trying again.';

            return;
        }

        RateLimiter::hit($rateLimitKey, (int) config('research.rate_limit_decay_seconds', 600));
        $this->errorMessage = null;
        $this->appendRevision($draft, 'draft');

        try {
            $interpretation = $interpreter->interpret($this->projectTitle, $this->researchQuestion);
            $this->appendRevision($draft, 'interpreted', $interpretation);
            $this->editing = false;
        } catch (Throwable) {
            $this->appendRevision($draft, 'failed');
            $this->errorMessage = 'We could not understand your description just now. Your draft is saved; please try again.';
        }
    }

    public function confirm(): void
    {
        $draft = $this->draftForCurrentSession();
        $revision = $draft?->currentRevision;

        if ($draft === null || $revision === null || $revision->status !== 'interpreted' || $revision->interpretation === null) {
            $this->errorMessage = 'Please review a current interpretation before confirming it.';

            return;
        }

        if ($revision->project_title !== $this->projectTitle || $revision->research_question !== $this->researchQuestion) {
            $this->editing = true;
            $this->errorMessage = 'Your description changed. Please ask us to understand it again before confirming.';

            return;
        }

        $this->appendRevision($draft, 'confirmed', ResearchInterpretation::fromArray($revision->interpretation));
        $this->editing = false;
        $this->errorMessage = null;
    }

    public function editDescription(): void
    {
        if ($this->draftForCurrentSession() !== null) {
            $this->editing = true;
            $this->errorMessage = null;
        }
    }

    private function invalidateInterpretationWhenChanged(string $property): void
    {
        $draft = $this->draftForCurrentSession();
        $revision = $draft?->currentRevision;

        if ($draft === null || $revision === null) {
            return;
        }

        if ($revision->project_title === $this->projectTitle && $revision->research_question === $this->researchQuestion) {
            return;
        }

        $this->editing = true;
        $this->errorMessage = null;

        try {
            $this->validate();
        } catch (ValidationException $exception) {
            $this->appendRevision($draft, 'draft', input: [
                'project_title' => $revision->project_title,
                'research_question' => $revision->research_question,
            ]);

            throw $exception;
        }

        $this->appendRevision($draft, 'draft');
    }

    private function createDraft(): ResearchDraft
    {
        $token = Str::random(64);
        $draft = ResearchDraft::query()->create([
            'public_id' => (string) Str::uuid(),
            'access_token_hash' => hash('sha256', $token),
            'current_revision' => 0,
        ]);

        session()->put('research_draft_public_id', $draft->public_id);
        session()->put('research_draft_access_token', $token);

        return $draft;
    }

    private function draftForCurrentSession(): ?ResearchDraft
    {
        $publicId = session()->get('research_draft_public_id');
        $token = session()->get('research_draft_access_token');

        if (! is_string($publicId) || ! is_string($token)) {
            return null;
        }

        return ResearchDraft::query()
            ->where('public_id', $publicId)
            ->where('access_token_hash', hash('sha256', $token))
            ->first();
    }

    private function currentRevision(): ?ResearchDraftRevision
    {
        return $this->draftForCurrentSession()?->currentRevision;
    }

    private function appendRevision(
        ResearchDraft $draft,
        string $status,
        ?ResearchInterpretation $interpretation = null,
        ?array $input = null,
    ): ResearchDraftRevision {
        return DB::transaction(function () use ($draft, $status, $interpretation, $input): ResearchDraftRevision {
            $draft = ResearchDraft::query()->lockForUpdate()->findOrFail($draft->getKey());
            $revisionNumber = $draft->current_revision + 1;
            $revision = $draft->revisions()->create([
                'revision' => $revisionNumber,
                'project_title' => $input['project_title'] ?? $this->projectTitle,
                'research_question' => $input['research_question'] ?? $this->researchQuestion,
                'interpretation' => $interpretation?->toArray(),
                'status' => $status,
                'confirmed_at' => $status === 'confirmed' ? now() : null,
            ]);

            $draft->forceFill(['current_revision' => $revisionNumber])->save();

            return $revision;
        });
    }

    public function render()
    {
        $revision = $this->currentRevision();
        $screen = $this->editing
            ? 'form'
            : match ($revision?->status) {
                'interpreted' => 'review',
                'confirmed' => 'ready',
                default => 'form',
            };

        return view('livewire.research-description-form', [
            'screen' => $screen,
            'interpretation' => $revision?->interpretation,
        ]);
    }
}
