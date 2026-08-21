# Theme build & development

This theme uses Tailwind CSS v4 directly from `assets/css/input.css`.

## Goals

- Produce a deterministic `assets/css/output.css` from `assets/css/input.css` using Tailwind.
- Keep the runtime CSS path aligned with the October layout: `assets/css/output.css`.
- Avoid extra wrapper scripts around Tailwind unless the app actually needs them.

## Commands

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

## Scripts

- `build`: runs `tailwindcss -i ./assets/css/input.css -o ./assets/css/output.css --minify`.
- `watch`: runs `tailwindcss -i ./assets/css/input.css -o ./assets/css/output.css --watch`.
- `publish`: runs the same build command.

Notes

- Tailwind scans source paths declared with `@source` in `assets/css/input.css`.
- Do not edit `assets/css/output.css` directly; edit `assets/css/input.css` and run the build.

Troubleshooting

- If `npm run build` fails with "Could not read package.json", ensure you are running from the repository root or use `npm --prefix themes/dataverse run build`.
- If a class is missing from the compiled CSS, make sure the file using it is covered by an `@source` entry in `assets/css/input.css`.
