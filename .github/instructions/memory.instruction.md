---
applyTo: '**'
---

# The DataVerse - Agent Memory / Instructions

This file stores persistent, project-specific guidance for the AI assistant working in the DataVerse workspace. Update when project preferences or conventions change.

Project summary:

- name: The DataVerse
- theme: Dark neon synthwave (background #0a0a0f; accents #0ff cyan, #f0f magenta)
- primary fonts: Tilt Neon (preferred), Orbitron (headings), Inter (body)
- platforms: OctoberCMS (octobertest2), Bookstack (wiki)
- top-level asset folder: `assets/` (fonts, images, CSS/JS vendor files)
- orchestrator app: `octobertest2/` (OctoberCMS 4.x, Laravel 12)

Development environment:

- Local: Laragon (PHP 8.5, MariaDB, Apache, Node.js for Tailwind builds)
- Production: iFastNet shared hosting; builds are packaged and uploaded manually (no Composer/NPM on server)

Coding focus and restrictions:

- Languages/frameworks: PHP, Twig-like templates (OctoberCMS), TailwindCSS, Tabulator.js, Formula.js, JavaScript, MediaWiki custom CSS/JS
- Never edit vendor/core files directly. Prefer extensions, wrappers, theme assets, or module overrides.
- Keep built assets in theme `assets/` where possible and reference via `|theme` or relative imports.
- When suggesting dependency installs, include exact commands (composer require or npm install) and prefer popular packages.

VS Code / Copilot preferences (extracted from workspace config):

- Project name in Copilot config: "The DataVerse"
- Copilot guidance highlights: include exact install commands when suggesting dependencies; favor dark theme colors; use Tailwind utilities over inline styles or Bootstrap; comment code meaningfully.
- Editor settings seen: `editor.formatOnSave: true`, `editor.defaultFormatter: esbenp.prettier-vscode`.
- File associations: `*.htm` and `*.twig` => html, `*.ini` => ini.
- Tailwind support: tailwindCSS should include html/php and uses `tailwind.config.js`.
- Files exclusion: node_modules and vendor are visible in the workspace (not hidden).

Behavioral instructions for the assistant (short contract):

- Inputs: user requests, repository files, and the in-repo Copilot/VSCode settings.
- Outputs: code edits, small runnable changes, suggestions aligned with the neon synthwave theme and the project's architecture.
- Error modes: if a requested change would modify vendor files or server-only workflows, warn and propose alternatives (plugins, wrappers, theme-level changes).
- Success criteria: changes don't touch vendor core files, follow Tailwind-first styling, include install commands for new deps, and keep local build steps for Laragon.

Operational notes:

- When adding or changing styles, run Tailwind build (`npm run build:css`) locally and verify  `assets/vendor/...` copy.
- For template changes, search for partial usage before editing (e.g., `themes/dataverse/partials/*`).
- Respect `.gitignore` rules; do not commit dev artifacts like node_modules. Built runtime assets that belong in `assets/vendor/` are acceptable.

Build commands (Windows / Laragon)

- Tailwind (build): `npm.cmd run build:css`  — builds Tailwind for the theme located at `themes/dataverse` and emits compiled CSS into the theme's `assets`/`dist` folder.
- Tailwind (watch): `npm.cmd run watch:css`  — watch mode for iterative development.
- Copy/vendor build (prefix & copy vendor CSS): `npm.cmd run build:vendor` or `npm.cmd run copy:vendor`.
- Full pipeline (build + vendor copy): `npm.cmd run build && npm.cmd run copy:vendor` or at repo root `npm.cmd run build:all` which wraps the necessary theme tasks.
- PostCSS-only tasks (if used by theme): `npm.cmd run build:postcss` and `npm.cmd run watch:postcss`.

Verification & success checks

- After a successful build, verify the compiled CSS exists (example paths): `themes/dataverse/assets/css/tailwind.css`, `themes/dataverse/dist/tailwind.css`, or `assets/vendor/...` depending on the build step.
- Confirm that no node_modules or other dev-only directories are committed. Only built vendor output (e.g., `assets/vendor/...`) should be committed when required.
- If a change affects templates, load the local Laragon site and verify the UI visually (nav, fonts, neon color tokens) and check the browser console for JS/CSS errors.
- When suggesting new dependencies, include exact install commands and recommend running installs locally (e.g., `npm install <pkg> --save-dev` or `composer require <vendor>/<package>`), then run the build steps above.

Short checklist for future edits:

- Prefer theme/asset-level changes over vendor edits
- Include exact install commands when recommending dependencies
- Keep Tailwind utilities and the neon color tokens consistent

If you want this memory adjusted (add more rules, make it stricter/looser, or include additional tooling notes), tell me what to change and I'll update this file.
