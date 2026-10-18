# Frontend Design

## Direction

The screen uses an editorial, quiet-workspace direction: warm paper background, deep ink typography, a restrained moss accent, and a small rust action color. A centered column gives the research prompt room to breathe. The form and a focused interpretation review are the product surfaces; there is no sidebar, dashboard chrome, marketing-section stack, progress bar, or chat metaphor.

Use system serif display typography paired with a readable system sans stack so the screen does not depend on a remote font service. CSS custom properties in `apps/web/resources/css/app.css` are the source of truth for color, spacing, type, and focus tokens.

## Components and accessibility

`<x-brand-mark>` provides the wordmark; the Livewire form owns labeled fields and errors, then transitions to a plain-language interpretation with one primary confirmation action. The confirmed state says dataset upload is next and that uploads are not available yet. Keep labels visible, errors associated with their inputs, focus rings clear, and layouts usable at narrow widths. Respect reduced-motion preferences and avoid decorative icon systems.

## Skills and guidance

No additional Codex skill was installed for this repository. The session exposes the built-in `imagegen` skill (`C:/Users/user/.codex/skills/.system/imagegen/SKILL.md`), which can be used for newly generated visual assets when the task calls for them. I checked the official OpenAI skills repository on 2026-10-18: its curated catalog does not contain a general `frontend-design` skill, and the expected path returned HTTP 404. The documented installer also requires network access and writes under `C:/Users/user/.codex/skills`, outside this workspace's writable roots; installation cannot be performed here. No third-party skill was installed. This document provides equivalent project guidance: choose a clear visual direction first, use a small token set, build the actual accessible screen, and inspect it at mobile and desktop widths.

## Review checklist

- Keep one clear primary action and no competing navigation.
- Check keyboard focus, labels, validation announcements, and contrast.
- Check the layout around 375 px and desktop widths.
- Show a clear in-progress state while interpretation runs and a generic retry message on provider failure.
- Do not imply that an interpretation is statistical analysis, or that uploads are already available.
