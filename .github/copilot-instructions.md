# Copilot / AI Agent Instructions — OctoberCMS codebase

Be concise. These notes focus on immediately actionable, discoverable patterns for making safe edits.

Big picture
- This is an OctoberCMS application (Laravel-based). Presentation lives in `themes/`, runtime modules under `modules/`, and core assets under `assets/` and `modules/*/assets/`.
- Templates use Twig-like syntax (`{{ ... }}`, `{% partial '...' %}`, `{% page %}`) and rely on October CMS to inject page variables (e.g. `this.page.title`).

Key files to read before changing UI or build behavior
- `package.json` — npm scripts: `build:css`, `watch:css`, `build:vendor`, `build:all`, `production`.
- `webpack.mix.js` and `webpack.config.js` — Mix configuration; copies many node modules into `modules/*/assets/vendor/`.
- `tailwind.config.js` and `src/tailwind-input.css` — Tailwind sources and content paths.
- `scripts/` — helper scripts (e.g. `run-tailwind.js`, `prefix-css-with-tv.js`, `vendor-bootstrap*.js`) used by npm scripts.
- A representative theme: `themes/dataverse/layouts/default.htm`, `themes/dataverse/partials/site/navbar.htm`, and `themes/dataverse/blueprints/` (content schema).

Build & developer workflows (Windows / PowerShell notes)
- Use the Windows-friendly npm shims shown in `package.json` (e.g. `npm.cmd run build:css`).
- Common commands:
  - Build compiled Tailwind: `npm.cmd run build:css`
  - Watch Tailwind: `npm.cmd run watch:css`
  - Build vendor CSS (prefix & copy): `npm.cmd run build:vendor`
  - Full pipeline (build vendor + clear October caches): `npm.cmd run build:all`
- VS Code tasks are preconfigured for these (Run Task → choose "Build all (full pipeline)" or the specific tailwind tasks).

Project conventions & patterns
- Assets served from theme `assets/` or copied into `modules/*/assets/` by Mix. Prefer adding new JS/CSS under `assets/js/` or `assets/css/` and referencing with the `|theme` filter: `{{ 'assets/css/style.css'|theme }}`.
- Internal URLs use the `|page` filter: `{{ 'cargo/index'|page }}` — avoid hardcoding internal links.
- Blueprints in `themes/*/blueprints/` define backend fields; update templates that reference those field names when you change blueprints.
- Vendor policy: do NOT edit vendored files in place. Add wrappers in `assets/js/` or `assets/css/` and document provenance in `assets/vendor/<lib>/README.txt` when vendoring third-party libs.

- When suggesting dependency installs, include the exact install command (e.g. `composer require vendor/package` or `npm install package --save`) and prefer minimal, popular packages.

- Project theme tokens: background color (hex) 0a0a0f, accents (hex) 0ff (cyan) and (hex) f0f (magenta). Prefer Orbitron for headings and Inter for body text; Tilt Neon may be used for display headings.

First actions for an AI agent
1. Read (in order): `package.json`, `webpack.mix.js`, `tailwind.config.js`, `scripts/run-tailwind.js`, and the theme files listed above.
2. If changing styles, run `npm.cmd run build:css` locally and verify `dist/tailwind.css` (or the final `assets/vendor/...` copy) is produced.
3. For template changes, search for uses of a partial before editing (e.g. `partials/site/*`) and update all includes.

What to avoid
- Don't rename/remove partials or blueprint fields without updating every template and page that uses them.
- Don't commit node_modules or dev-only artifacts; commit only built outputs meant for runtime (e.g. vendored `assets/vendor/...` CSS).

If anything here is unclear or you need more examples (specific partials, blueprint fields, or build logs), tell me which area and I will expand the guidance with concrete snippets.
