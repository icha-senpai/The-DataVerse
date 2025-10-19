Theme build & development
=========================

This README documents the theme-level build and watch workflow for `themes/dataverse`.

Goals

- Produce a deterministic `assets/css/output.css` from `assets/css/input.css` using Tailwind/PostCSS.

- Copy the compiled output into `assets/vendor/tailwind/tailwind.css` for OctoberCMS to serve.

- Provide a single-run build script and a watch mode that uses the same flow to avoid divergence.

Commands (Windows / PowerShell)

From the repo root:

```powershell
npm --prefix themes/dataverse run build
npm --prefix themes/dataverse run watch
```

From the theme folder (`themes/dataverse`):

```powershell
cd themes/dataverse
npm run build
npm run watch
```

PostCSS variant (if you prefer PostCSS CLI instead of Tailwind CLI):

```powershell
npm --prefix themes/dataverse run build:postcss
npm --prefix themes/dataverse run watch:postcss
```

What the scripts do

- `build` (node ./scripts/build-and-copy.js): runs Tailwind (or PostCSS) once to build `assets/css/output.css`, then copies it to `assets/vendor/tailwind/tailwind.css`.

- `watch` (node ./scripts/watch-and-copy-clean.js): watches source files and runs the same single-run build script on changes (debounced + queued). This ensures the watch flow matches the build flow exactly.

- `copy:vendor` (node ./scripts/copy-to-vendor.js): copy helper used by scripts.

- `test:vendor` (node ./scripts/test-vendor-updated.js): runs the build and verifies vendor file matches `output.css`.

Notes

- The watcher runs `node ./scripts/build-and-copy.js` on changes and avoids spawning `npx tailwindcss --watch` directly.

- We intentionally keep a vendor copy (`assets/vendor/tailwind/tailwind.css`) for the runtime so OctoberCMS can serve a static file without running the build in production.

- Do not edit files under `assets/vendor/` directly; edit `assets/css/input.css` and run the build.

Troubleshooting

- If `npm run build` fails with "Could not read package.json", ensure you are running from the repository root or use `npm --prefix themes/dataverse run build`.

- If the watcher doesn't trigger, check that Node has permission to watch files on your platform and try the PostCSS variant if your system's tailwind watcher is unreliable.

Contact

- If you'd like me to add CI checks to verify the vendor file is up-to-date as part of PR checks, say so and I'll add a test script and an npm script to fail when `output.css` and vendor files diverge.
