# Copilot / AI Agent Instructions for the Dataverse theme

Keep guidance concise. When making edits, reference concrete files and patterns below.

What this project is
- An OctoberCMS theme located at the repository root (`theme.yaml` present). HTML templates use Twig-like tags: `{{ ... }}` and `{% partial '...' %}`/`{% page %}`.
- Layouts live in `layouts/` (example: `layouts/default.htm`). Pages are under `pages/`. Partials are in `partials/` (site navbar/footer live at `partials/site/*`).

Big-picture architecture (why things are organized this way)
- Theme as presentation layer only: templates and assets (CSS/JS) are included in the theme. Backend content is provided by OctoberCMS (pages, CMS components, blueprints in `blueprints/`).
- Blueprints under `blueprints/` declare editor forms/streams. Example: `blueprints/blog/post.yaml` shows stream-type content definitions and mixins.
- Assets are served from the theme's `assets/` directory. Use theme filters to reference them in templates (e.g. `{{ 'assets/css/style.css'|theme }}`).

Key files and patterns to reference
- `theme.yaml` — theme metadata. Minimal; used by OctoberCMS to register the theme.
- `layouts/default.htm` — base HTML shell. Look for `{{ this.page.title }}`, `{% partial 'site/navbar' %}`, and `{% page %}` placeholders.
- `partials/site/navbar.htm` — concrete example of using `|page` filter to build internal links (e.g. `{{ 'home'|page }}`). Use these filters when adding links or navigation.
- `blueprints/` — content schema for backend editors. `blog/post.yaml` demonstrates stream types, mixin fields and navigation labels.
- `assets/` — CSS, JS and third-party libs. Prefer adding new frontend code under `assets/js/` and `assets/css/` and reference via `|theme` filter.

Tailwind CSS and vendoring
- This theme manages assets in `assets/`. If you introduce Tailwind CSS, vendor the compiled output into `assets/vendor/tailwind/` (do not modify existing upstream vendor folders).
- Recommended workflow (keep optional and documented):
  1. Add a small Node toolchain at the theme root (optional): `package.json`, `tailwind.config.js`, `postcss.config.js`, and a `src/` input CSS file.
  2. Build a single compiled CSS file (example output: `dist/tailwind.css`) during development, then copy/commit the compiled file into `assets/vendor/tailwind/tailwind.css` for deployment.

Example build (PowerShell):

```powershell
npm install
npx tailwindcss -i ./src/tailwind-input.css -o ./dist/tailwind.css 
```

Reference the vendored build in templates with `|theme`:

```html
<link rel="stylesheet" href="{{ 'assets/vendor/tailwind/tailwind.css'|theme }}">
```

- Why vendor: OctoberCMS themes are often deployed without running Node on the server; committing compiled CSS ensures the site has styles without a server-side build step.
- Keep Tailwind sources in the repo only if you document how to rebuild; otherwise, treat compiled CSS as the canonical shipped artifact.

Project-specific conventions and patterns
- Twig-like filters used throughout:
  - `|theme` to point to theme asset paths (e.g. `{{ 'assets/css/style.css'|theme }}`).
  - `|page` to generate internal page URLs (e.g. `{{ 'cargo/index'|page }}`).
- Partials are reusable template fragments under `partials/`. Insert with `{% partial 'path/to/partial' %}`.
- Pages insert content with `{% page %}` inside layouts.
- Blueprints use October's stream-based configuration. When updating data shapes, mirror field names in templates.
- Vendor libraries ship under `assets/vendor/`. Avoid modifying upstream vendor files; instead add wrappers in `assets/js/` or `assets/css/`.

Build / dev / debugging workflows (discoverable from the repo)
- There is no package.json, build script, or explicit task runner in the theme. Typical workflows:
  - Local preview: run OctoberCMS in your usual dev environment (this theme folder should be placed in October's themes directory). If you're using Laragon (likely), start the local web server and open the site.
  - Static asset changes: editing files under `assets/` will be picked up by October when the page reloads; no bundling step present.
  - If you add Node-based tooling, include `package.json` and document commands in README.

Testing and linting
- There are no tests or linters configured in the theme. If you add tooling, keep it opt-in (don't modify theme runtime files without adding clear migration notes).

Integration points & external dependencies
- OctoberCMS runtime provides page data, page title (this.page), and streams defined by blueprints. Expect page-level variables like `this.page.title`.
- Third-party frontend libraries live under `assets/vendor/` (examples: `monaco-editor`, `moment`, `chart.js`). When referencing these in templates, prefer the minified UMD or ESM files provided (see `assets/vendor/*`).

Never download minified vendor files (policy)
- Policy: do NOT download or commit third-party "*.min.*" files directly from CDNs when possible. Instead vendor the unminified source or fetch the upstream source (repo or package) and build/minify locally in `dev/`.
- Why: minified files lack provenance (hard to inspect), reduce ability to audit or patch, and sometimes strip important metadata or source maps. Building locally preserves reproducibility and lets us pin exact source versions.
- When to fetch minified: only allowed if the upstream project provides only a prebuilt minified bundle with a clear license and source mapping — document this in the vendor README and prefer an upstream tarball or Git tag instead.
  Caveat: only install, commit, or reference a *.min.* build when there is no available full/unminified build from the upstream project (or you cannot reproduce it locally). If an unminified/dist artifact exists upstream, prefer that one and vendor it instead; if you must ship a minified file, build or minify it locally from the full source and record provenance in the vendor README.
- How to vendor (PowerShell example): fetch non-minified releases from unpkg/git or download the source and build locally.

PowerShell example — download non-minified (preferred):

```powershell
# create folder then download the unminified (full) JS/CSS from unpkg or dist folder
New-Item -ItemType Directory -Force -Path .\assets\vendor\tabulator\
Invoke-WebRequest -Uri "https://unpkg.com/tabulator-tables@6.3.1/dist/js/tabulator.js" -OutFile .\assets\vendor\tabulator\tabulator.js
Invoke-WebRequest -Uri "https://unpkg.com/tabulator-tables@6.3.1/dist/css/tabulator.css" -OutFile .\assets\vendor\tabulator\tabulator.css

# If only a source repo is available, clone or download the tagged release and build per upstream docs, then copy built outputs into assets/vendor/
``` 

PowerShell example — build/minify locally (if you need a minified file):

```powershell
# From the project dev folder (e.g., dev/), install and run the build tooling and then copy/minify into assets/vendor/
cd .\dev\
npm.cmd install
npm.cmd run build:css:full   # or the project's build script
Copy-Item -Path ..\dist\tailwind.css -Destination ..\assets\vendor\tailwind\tailwind.css -Force

# Optional local minify step using a CLI (if you must create a .min.css):
npx clean-css-cli -o ..\assets\vendor\tailwind\tailwind.min.css ..\dist\tailwind.css
```

Examples to copy from the codebase
- Linking a theme asset (safe copy):
  - ` <link rel="stylesheet" href="{{ 'assets/css/style.css'|theme }}">` (from `layouts/default.htm`)
- Building internal page links:
  - ` <a href="{{ 'cargo/index'|page }}">Cargo</a>` (from `partials/site/navbar.htm`)
- Navigation dropdown pattern: `partials/site/navbar.htm` uses nested <ul>/.dropdown classes; follow this markup when adding new menus.

When editing templates:
- Keep placeholders intact: `{% page %}`, `{% partial '...' %}`, `{{ this.page.title }}`. Changing these will impact how October injects content.
- Avoid hardcoding absolute URLs for internal pages; use `|page` filter so October can manage routing.

What an AI agent should do first
1. Read `theme.yaml`, `layouts/default.htm`, `partials/site/navbar.htm`, and `blueprints/` before making layout or content changes. They define the theme's structure and data.
2. For frontend work, check `assets/vendor/` for existing libraries to reuse.
3. If adding new fields or content types, update `blueprints/` and any templates that read those fields.

What to avoid
- Don't delete or rename `partials/site/*` without updating every layout and page that includes them.
- Don't edit vendor files under `assets/vendor/`; instead add wrappers under `assets/js/` or `assets/css/`.

If you need more info
- Tell me what you plan to change and I will add focused examples and a brief checklist to ensure compatibility with OctoberCMS.

---

Fetching external UI/CSS assets (CDN vs vendoring)
- Prefer vendoring UI libraries and CSS assets into `assets/vendor/` when the library is essential to the site's layout or when your deployment environment cannot run Node or rely on remote CDNs.
- Use a CDN only for non-critical or optional assets where external availability is acceptable and the vendor's SLA is trusted.
- When vendoring from the web, capture the exact file version and license. Put files under a clear subfolder, e.g. `assets/vendor/bootstrap/` or `assets/vendor/tailwind/`.
- Example PowerShell download (save a CSS file into the vendor folder):

```powershell
# create folder then download published CSS (prefer the unminified/dist file when available)
New-Item -ItemType Directory -Force -Path .\assets\vendor\some-ui\
Invoke-WebRequest -Uri "https://unpkg.com/some-ui@1.2.3/dist/some-ui.css" -OutFile .\assets\vendor\some-ui\some-ui.css
```

- Record the source and license in a short text file next to vendored files (e.g. `assets/vendor/some-ui/README.txt`) containing URL, version, and license.
- For security, prefer vendoring whole files provided by the author and, when possible, include an `integrity` checksum in template references if you continue to use remote CDNs.
- Avoid mixing multiple versions of the same library across `assets/vendor/`; consolidate or namespace them to avoid runtime collisions.

If this looks good, I can tweak tone/length or merge in any existing agent docs you want preserved; tell me if you'd like the file relocated or expanded.

Rebuild note for agents
- For rebuilding Tailwind locally, use the `dev/` toolchain included in the repo (`dev/README.md` explains install & build). Do not commit `dev/node_modules`.