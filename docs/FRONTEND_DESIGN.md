# Frontend Design

## Direction

The first screen uses an editorial, quiet-workspace direction: warm paper background, deep ink typography, a restrained moss accent, and a small rust action color. A centered column gives the research prompt room to breathe. The form is the product surface; there is no sidebar, dashboard chrome, marketing-section stack, progress bar, or chat metaphor.

Use system serif display typography paired with a readable system sans stack so the screen does not depend on a remote font service. CSS custom properties in `apps/web/resources/css/app.css` are the source of truth for color, spacing, type, and focus tokens.

## Components and accessibility

`<x-brand-mark>` provides the wordmark; the Livewire research-description form owns labeled fields, validation, a single primary action, and an honest preview status. Keep labels visible, errors associated with their inputs, focus rings clear, and layouts usable at narrow widths. Respect reduced-motion preferences and avoid decorative icon systems.

## Skills and guidance

No additional Codex skill was installed for this repository. The session exposes the built-in `imagegen` skill (`C:/Users/user/.codex/skills/.system/imagegen/SKILL.md`), which can be used for newly generated visual assets when the task calls for them. The official OpenAI [`frontend-design` skill](https://github.com/openai/skills) was confirmed in the OpenAI skills catalog; it is not enabled in this session. Its documented installer requires network access and writes under the Codex skills directory, which is outside this workspace's write access. No third-party skill was installed. This document provides equivalent project guidance: choose a clear visual direction first, use a small token set, build the actual accessible screen, and inspect it at mobile and desktop widths.

## Review checklist

- Keep one clear primary action and no competing navigation.
- Check keyboard focus, labels, validation announcements, and contrast.
- Check the layout around 375 px and desktop widths.
- Do not imply AI processing or data retention when those services are not implemented.
