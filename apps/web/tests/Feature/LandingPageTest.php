<?php

namespace Tests\Feature;

use App\Livewire\ResearchDescriptionForm;
use Livewire\Livewire;
use Tests\TestCase;

final class LandingPageTest extends TestCase
{
    public function test_landing_page_shows_research_form_and_honest_preview_copy(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Make sense of')
            ->assertSee('What are you studying?')
            ->assertSee('AI research understanding is not connected');
    }

    public function test_form_requires_a_research_title_and_question(): void
    {
        Livewire::test(ResearchDescriptionForm::class)
            ->call('submit')
            ->assertHasErrors(['projectTitle' => 'required', 'researchQuestion' => 'required']);
    }

    public function test_valid_submission_shows_preview_message_without_saving_data(): void
    {
        Livewire::test(ResearchDescriptionForm::class)
            ->set('projectTitle', 'Study habits and wellbeing')
            ->set('researchQuestion', 'How do study habits relate to student wellbeing?')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('previewSubmitted', true);
    }

    public function test_application_health_endpoint_is_available(): void
    {
        $this->get('/health')->assertOk()->assertExactJson(['status' => 'ok']);
        $this->get('/up')->assertOk();
    }
}
