<form class="research-form" wire:submit="submit" novalidate>
    <div class="field-group">
        <label for="project-title">What are you studying?</label>
        <input
            id="project-title"
            type="text"
            wire:model="projectTitle"
            autocomplete="off"
            maxlength="120"
            placeholder="e.g. Student wellbeing and study habits"
            aria-describedby="project-title-help @error('projectTitle') project-title-error @enderror"
            @error('projectTitle') aria-invalid="true" @enderror
        >
        <p class="field-help" id="project-title-help">A working title is enough to begin.</p>
        @error('projectTitle') <p class="field-error" id="project-title-error">{{ $message }}</p> @enderror
    </div>

    <div class="field-group question-group">
        <label for="research-question">What would you like to understand?</label>
        <textarea
            id="research-question"
            wire:model="researchQuestion"
            rows="4"
            maxlength="2000"
            placeholder="Describe the question you’re exploring and what you hope your research will show."
            aria-describedby="research-question-help @error('researchQuestion') research-question-error @enderror"
            @error('researchQuestion') aria-invalid="true" @enderror
        ></textarea>
        <p class="field-help" id="research-question-help">Write in your own words. You can refine it later.</p>
        @error('researchQuestion') <p class="field-error" id="research-question-error">{{ $message }}</p> @enderror
    </div>

    <div class="form-action">
        <button class="primary-button" type="submit" wire:loading.attr="disabled" wire:target="submit">
            <span wire:loading.remove wire:target="submit">Continue with your research <span aria-hidden="true">↗</span></span>
            <span wire:loading wire:target="submit">Checking your description…</span>
        </button>
        <p class="form-footnote">AI research understanding is not connected yet.</p>
    </div>

    @if ($previewSubmitted)
        <p class="preview-notice" role="status">Your description looks ready. AI research understanding is not connected in this preview.</p>
    @endif
</form>
