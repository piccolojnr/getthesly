<?php

namespace Tests\Feature;

use App\Ai\Agents\UnderstandResearch;
use App\Livewire\ResearchDescriptionForm;
use App\Models\ResearchDraft;
use App\Services\Research\ResearchInterpretation;
use App\Services\Research\ResearchInterpreter;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

final class ResearchUnderstandingTest extends TestCase
{
    /** @return array{summary: string, objective: string, concepts: list<string>, clarification: ?string} */
    private function validInterpretation(): array
    {
        return [
            'summary' => 'You want to understand how social media use relates to academic performance.',
            'objective' => 'Explore the relationship between social media use and academic performance.',
            'concepts' => ['Social media use', 'Academic performance'],
            'clarification' => null,
        ];
    }

    private function startForm()
    {
        return Livewire::test(ResearchDescriptionForm::class)
            ->set('projectTitle', 'Social media and academic performance')
            ->set('researchQuestion', 'How is social media use related to students’ academic performance?');
    }

    private function appendConcurrentRevision(ResearchDraft $draft, string $status, string $title, string $question): void
    {
        $draft->refresh();
        $revision = $draft->current_revision + 1;
        $draft->revisions()->create([
            'revision' => $revision,
            'project_title' => $title,
            'research_question' => $question,
            'interpretation' => $status === 'confirmed' ? $this->validInterpretation() : null,
            'status' => $status,
            'confirmed_at' => $status === 'confirmed' ? now() : null,
        ]);
        $draft->forceFill(['current_revision' => $revision])->save();
    }

    public function test_valid_input_is_interpreted_and_saved_as_a_private_draft(): void
    {
        UnderstandResearch::fake([$this->validInterpretation()])->preventStrayPrompts();

        $this->startForm()
            ->call('understand')
            ->assertHasNoErrors()
            ->assertSee('Here’s what I understand')
            ->assertSee($this->validInterpretation()['summary'])
            ->assertSee('That’s correct')
            ->assertSee('Edit description');

        $draft = ResearchDraft::query()->with('revisions')->firstOrFail();
        $revision = $draft->currentRevision;

        $this->assertSame('interpreted', $revision->status);
        $this->assertSame('Social media and academic performance', $revision->project_title);
        $this->assertSame($this->validInterpretation(), $revision->interpretation);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $draft->public_id);
        $this->assertSame(hash('sha256', session()->get('research_draft_access_token')), $draft->access_token_hash);
        $this->assertNotSame(session()->get('research_draft_access_token'), $draft->access_token_hash);
        UnderstandResearch::assertPromptedTimes(1);
    }

    public function test_invalid_input_does_not_call_the_ai_provider(): void
    {
        UnderstandResearch::fake()->preventStrayPrompts();

        Livewire::test(ResearchDescriptionForm::class)
            ->call('understand')
            ->assertHasErrors(['projectTitle' => 'required', 'researchQuestion' => 'required']);

        UnderstandResearch::assertNeverPrompted();
        $this->assertDatabaseCount('research_drafts', 0);
    }

    public function test_malformed_structured_output_is_not_displayed_as_an_interpretation(): void
    {
        UnderstandResearch::fake([[
            'summary' => str_repeat('x', 501),
            'objective' => 'Explore the stated relationship.',
            'concepts' => ['Social media use'],
            'clarification' => null,
        ]]);

        $this->startForm()
            ->call('understand')
            ->assertSee('We could not understand your description just now.')
            ->assertDontSee('Here’s what I understand');

        $this->assertSame('failed', ResearchDraft::query()->firstOrFail()->currentRevision->status);
    }

    public function test_interpretation_with_a_clarification_cannot_be_confirmed(): void
    {
        $interpretation = $this->validInterpretation();
        $interpretation['clarification'] = 'Which student group are you referring to?';
        UnderstandResearch::fake([$interpretation]);

        $this->startForm()
            ->call('understand')
            ->assertSee('Which student group are you referring to?')
            ->assertDontSee('That’s correct')
            ->call('confirm')
            ->assertSee('Please edit your description to clarify this question before confirming.');

        $this->assertSame('interpreted', ResearchDraft::query()->firstOrFail()->currentRevision->status);
    }

    public function test_provider_failure_shows_a_safe_retry_message_without_logging_the_research_text(): void
    {
        $interpreter = $this->mock(ResearchInterpreter::class);
        $interpreter->shouldReceive('interpret')->once()->andThrow(new RuntimeException('private-provider-detail'));

        $this->startForm()
            ->call('understand')
            ->assertSee('Your draft is saved; please try again.')
            ->assertDontSee('private-provider-detail');

        $this->assertSame('failed', ResearchDraft::query()->firstOrFail()->currentRevision->status);
    }

    public function test_provider_timeout_shows_the_same_safe_retry_message(): void
    {
        $interpreter = $this->mock(ResearchInterpreter::class);
        $interpreter->shouldReceive('interpret')->once()->andThrow(new ConnectionException('provider request timed out'));

        $this->startForm()
            ->call('understand')
            ->assertSee('We could not understand your description just now.')
            ->assertDontSee('provider request timed out');
    }

    public function test_delayed_interpretation_cannot_replace_a_revision_created_by_an_edit(): void
    {
        $interpreter = $this->mock(ResearchInterpreter::class);
        $interpreter->shouldReceive('interpret')->once()->andReturnUsing(function () {
            $draft = ResearchDraft::query()->firstOrFail();
            $this->appendConcurrentRevision($draft, 'draft', 'A changed study', 'How does sleep duration relate to university grades?');

            return ResearchInterpretation::fromArray($this->validInterpretation());
        });

        $this->startForm()->call('understand')->assertSee('description changed while we were working');

        $draft = ResearchDraft::query()->firstOrFail();
        $this->assertSame('draft', $draft->currentRevision->status);
        $this->assertSame('A changed study', $draft->currentRevision->project_title);
        $this->assertSame(2, $draft->revisions()->count());
    }

    public function test_delayed_interpretation_cannot_replace_a_newer_retry(): void
    {
        $interpreter = $this->mock(ResearchInterpreter::class);
        $interpreter->shouldReceive('interpret')->once()->andReturnUsing(function () {
            $draft = ResearchDraft::query()->firstOrFail();
            $this->appendConcurrentRevision($draft, 'draft', 'Social media and academic performance', 'How is social media use related to students’ academic performance?');

            return ResearchInterpretation::fromArray($this->validInterpretation());
        });

        $this->startForm()->call('understand')->assertSee('description changed while we were working');

        $draft = ResearchDraft::query()->firstOrFail();
        $this->assertSame('draft', $draft->currentRevision->status);
        $this->assertSame(2, $draft->revisions()->count());
    }

    public function test_delayed_interpretation_cannot_replace_a_confirmed_newer_revision(): void
    {
        $interpreter = $this->mock(ResearchInterpreter::class);
        $interpreter->shouldReceive('interpret')->once()->andReturnUsing(function () {
            $draft = ResearchDraft::query()->firstOrFail();
            $title = 'Social media and academic performance';
            $question = 'How is social media use related to students’ academic performance?';
            $this->appendConcurrentRevision($draft, 'interpreted', $title, $question);
            $this->appendConcurrentRevision($draft, 'confirmed', $title, $question);

            return ResearchInterpretation::fromArray($this->validInterpretation());
        });

        $this->startForm()->call('understand')->assertSee('description changed while we were working');

        $draft = ResearchDraft::query()->firstOrFail();
        $this->assertSame('confirmed', $draft->currentRevision->status);
        $this->assertSame(3, $draft->revisions()->count());
    }

    public function test_editing_after_interpretation_invalidates_it_and_preserves_the_previous_revision(): void
    {
        UnderstandResearch::fake([$this->validInterpretation()]);

        $component = $this->startForm()->call('understand');
        $draft = ResearchDraft::query()->firstOrFail();
        $interpretedRevision = $draft->current_revision;

        $component->set('researchQuestion', 'How does sleep duration relate to university grades?')
            ->assertSee('Understand my research')
            ->assertDontSee('Here’s what I understand');

        $draft->refresh();
        $this->assertSame('draft', $draft->currentRevision->status);
        $this->assertNull($draft->currentRevision->interpretation);
        $this->assertSame('interpreted', $draft->revisions()->where('revision', $interpretedRevision)->firstOrFail()->status);
    }

    public function test_invalid_edit_invalidates_the_old_interpretation_without_persisting_oversized_input(): void
    {
        UnderstandResearch::fake([$this->validInterpretation()]);
        $originalQuestion = 'How is social media use related to students’ academic performance?';

        $component = $this->startForm()->call('understand');
        $component->set('researchQuestion', str_repeat('x', 2001))
            ->assertHasErrors(['researchQuestion' => 'max'])
            ->assertSee('Understand my research')
            ->assertDontSee('Here’s what I understand');

        $revision = ResearchDraft::query()->firstOrFail()->currentRevision;
        $this->assertSame('draft', $revision->status);
        $this->assertSame($originalQuestion, $revision->research_question);
        $this->assertNull($revision->interpretation);
    }

    public function test_confirmation_is_persisted_and_restored_from_the_same_session(): void
    {
        UnderstandResearch::fake([$this->validInterpretation()]);

        $this->startForm()
            ->call('understand')
            ->call('confirm')
            ->assertSee('Your research brief is ready.')
            ->assertSee('uploads are not available yet');

        $draft = ResearchDraft::query()->firstOrFail();
        $confirmed = $draft->currentRevision;
        $this->assertSame('confirmed', $confirmed->status);
        $this->assertNotNull($confirmed->confirmed_at);

        Livewire::test(ResearchDescriptionForm::class)
            ->assertSet('projectTitle', 'Social media and academic performance')
            ->assertSee('Your research brief is ready.');
    }

    public function test_repeated_confirmation_does_not_append_duplicate_confirmed_revisions(): void
    {
        UnderstandResearch::fake([$this->validInterpretation()]);
        $component = $this->startForm()->call('understand')->call('confirm')->call('confirm');

        $draft = ResearchDraft::query()->firstOrFail();
        $this->assertSame('confirmed', $draft->currentRevision->status);
        $this->assertSame(1, $draft->revisions()->where('status', 'confirmed')->count());
        $component->assertSee('Please review a current interpretation before confirming it.');
    }

    public function test_stale_interpretation_cannot_be_confirmed_after_editing(): void
    {
        UnderstandResearch::fake([$this->validInterpretation()]);

        $this->startForm()
            ->call('understand')
            ->set('projectTitle', 'A different study')
            ->call('confirm')
            ->assertSee('Please review a current interpretation before confirming it.');

        $draft = ResearchDraft::query()->firstOrFail();
        $this->assertSame('draft', $draft->currentRevision->status);
        $this->assertNull($draft->currentRevision->confirmed_at);
    }

    public function test_another_browser_session_cannot_load_the_draft(): void
    {
        UnderstandResearch::fake([$this->validInterpretation()]);
        $this->startForm()->call('understand');
        $this->assertDatabaseCount('research_drafts', 1);

        $publicId = ResearchDraft::query()->firstOrFail()->public_id;
        session()->flush();
        session()->put('research_draft_public_id', $publicId);

        Livewire::test(ResearchDescriptionForm::class)
            ->assertSet('projectTitle', '')
            ->assertDontSee('Here’s what I understand');
    }

    public function test_rate_limit_prevents_repeated_provider_calls(): void
    {
        config(['research.rate_limit_attempts' => 1]);
        config(['research.rate_limit_decay_seconds' => 600]);

        $interpreter = $this->mock(ResearchInterpreter::class);
        $interpreter->shouldReceive('interpret')->once()->andReturn(ResearchInterpretation::fromArray($this->validInterpretation()));

        $component = $this->startForm()->call('understand');
        $component->call('understand')->assertSee('Please wait a few minutes before trying again.');
    }

    public function test_ip_rate_limit_blocks_requests_even_with_a_fresh_session(): void
    {
        config(['research.rate_limit_ip_attempts' => 1]);
        config(['research.rate_limit_decay_seconds' => 600]);
        $ipKey = 'research-understanding:ip:'.hash('sha256', (string) request()->ip());
        RateLimiter::hit($ipKey, 600);
        session()->regenerate();

        $interpreter = $this->mock(ResearchInterpreter::class);
        $interpreter->shouldNotReceive('interpret');

        $this->startForm()
            ->call('understand')
            ->assertSee('Please wait a few minutes before trying again.');
    }

    public function test_visiting_a_saved_draft_refreshes_its_retention_activity(): void
    {
        $token = Str::random(64);
        $draft = ResearchDraft::query()->create([
            'public_id' => (string) Str::uuid(),
            'access_token_hash' => hash('sha256', $token),
            'current_revision' => 1,
            'last_active_at' => now()->subDays(29),
        ]);
        $draft->revisions()->create([
            'revision' => 1,
            'project_title' => 'An active study',
            'research_question' => 'How does sleep duration relate to university grades?',
            'status' => 'draft',
        ]);
        session()->put('research_draft_public_id', $draft->public_id);
        session()->put('research_draft_access_token', $token);

        Livewire::test(ResearchDescriptionForm::class)
            ->assertSet('projectTitle', 'An active study');

        $this->assertTrue($draft->fresh()->last_active_at->greaterThan(now()->subMinute()));
        $this->artisan('research:drafts:prune')->assertSuccessful();
        $this->assertDatabaseHas('research_drafts', ['id' => $draft->id]);
    }

    public function test_draft_cleanup_removes_expired_drafts_and_their_revisions(): void
    {
        $draft = ResearchDraft::query()->create([
            'public_id' => (string) Str::uuid(),
            'access_token_hash' => hash('sha256', Str::random(64)),
            'current_revision' => 1,
        ]);
        $draft->timestamps = false;
        $draft->forceFill([
            'created_at' => now()->subDays(31),
            'updated_at' => now()->subDays(31),
            'last_active_at' => now()->subDays(31),
        ])->saveQuietly();
        $draft->revisions()->create([
            'revision' => 1,
            'project_title' => 'An old title',
            'research_question' => 'An old research question that is long enough.',
            'status' => 'draft',
        ]);

        $this->artisan('research:drafts:prune')->assertSuccessful();

        $this->assertDatabaseMissing('research_drafts', ['id' => $draft->id]);
        $this->assertDatabaseCount('research_draft_revisions', 0);
    }
}

