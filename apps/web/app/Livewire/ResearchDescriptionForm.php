<?php

namespace App\Livewire;

use Livewire\Attributes\Validate;
use Livewire\Component;

final class ResearchDescriptionForm extends Component
{
    #[Validate('required|string|max:120')]
    public string $projectTitle = '';

    #[Validate('required|string|min:20|max:2000')]
    public string $researchQuestion = '';

    public bool $previewSubmitted = false;

    public function updated(string $property): void
    {
        if (in_array($property, ['projectTitle', 'researchQuestion'], true)) {
            $this->previewSubmitted = false;
        }
    }

    public function submit(): void
    {
        $this->previewSubmitted = false;
        $this->validate();
        $this->previewSubmitted = true;
    }

    public function render()
    {
        return view('livewire.research-description-form');
    }
}
