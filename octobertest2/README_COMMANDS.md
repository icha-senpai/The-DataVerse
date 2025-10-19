# README_COMMANDS

This repository includes helper commands for building Tailwind and vendor CSS on Windows/PowerShell.

Quick commands (PowerShell):

- Build Tailwind CSS once (uses local tailwind binary or node wrapper):

```powershell
npm.cmd run build:css
```

- Watch for changes:

```powershell
npm.cmd run watch:css
```

- Build and prefix for the theme's vendor folder:

```powershell
npm.cmd run build:vendor
```

- Full pipeline (build, prefix, copy, clear October caches):

```powershell
npm.cmd run build:all
```

Notes:

- If `npm run` fails due to PowerShell execution policy, use the `.cmd` shim as shown above, or adjust your ExecutionPolicy.
- You can run these tasks from VS Code: Terminal → Run Task... → pick "Build all (full pipeline)" or another task.
