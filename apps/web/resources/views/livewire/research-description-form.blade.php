@if ($screen === 'form')
    <form class="research-form" wire:submit="understand" novalidate aria-label="Research description">
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
            @error('projectTitle') <p class="field-error" id="project-title-error" role="alert">{{ $message }}</p> @enderror
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
            <p class="field-help" id="research-question-help">Write in your own words. No statistical terms are needed.</p>
            @error('researchQuestion') <p class="field-error" id="research-question-error" role="alert">{{ $message }}</p> @enderror
        </div>

        @if ($errorMessage)
            <p class="preview-notice" role="alert">{{ $errorMessage }}</p>
        @endif

        <div class="form-action">
            <button class="primary-button" type="submit" wire:loading.attr="disabled" wire:target="understand">
                <span wire:loading.remove wire:target="understand">{{ $errorMessage ? 'Try again' : 'Understand my research' }} <span aria-hidden="true">↗</span></span>
                <span wire:loading wire:target="understand" role="status" aria-live="polite">Making sense of your description…</span>
            </button>
            <p class="form-footnote">Sent to our AI provider. Drafts are encrypted and deleted after 30 inactive days.</p>
        </div>
    </form>
@elseif ($screen === 'review')
    <section class="research-review" aria-labelledby="review-heading" aria-live="polite">
        <p class="eyebrow"><span class="eyebrow-rule"></span> A thoughtful first read</p>
        <h2 id="review-heading">Here’s what I understand</h2>
        <p class="review-summary">{{ $interpretation['summary'] }}</p>
        <div class="review-detail">
            <h3>Your objective</h3>
            <p>{{ $interpretation['objective'] }}</p>
        </div>
        @if ($interpretation['concepts'] !== [])
            <div class="review-detail">
                <h3>Ideas in your question</h3>
                <ul class="concept-list">
                    @foreach ($interpretation['concepts'] as $concept)
                        <li>{{ $concept }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if ($interpretation['clarification'])
            <p class="clarification-note"><strong>One thing to clarify:</strong> {{ $interpretation['clarification'] }}</p>
        @endif
        <p class="review-boundary">This is an interpretation of your description. No data has been analyzed and no statistical method has been selected.</p>
        @if ($errorMessage)
            <p class="preview-notice" role="alert">{{ $errorMessage }}</p>
        @endif
        <div class="form-action review-actions">
            <button class="primary-button" type="button" wire:click="confirm">That’s correct</button>
            <button class="text-button" type="button" wire:click="editDescription">Edit description</button>
        </div>
    </section>
@else
    <section class="research-ready" aria-labelledby="ready-heading" aria-live="polite">
        <p class="eyebrow"><span class="eyebrow-rule"></span> Description confirmed</p>
        <h2 id="ready-heading">Your research brief is ready.</h2>
        <p>{{ $interpretation['summary'] }}</p>
        <div class="preview-notice" role="status">Your interpretation is saved for this browser session. Adding a dataset is the next step; uploads are not available yet.</div>
    </section>
@endif
