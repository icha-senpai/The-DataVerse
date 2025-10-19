Formula.js vendored files
Source: https://unpkg.com/formulajs/
Files:
Version: latest from unpkg at time of download
License: MIT (see upstream)

Policy: DO NOT download or commit minified files directly from CDNs without also vendoring the unminified source and recording provenance.

Preferred workflow (PowerShell):
New-Item -ItemType Directory -Force -Path .\assets\vendor\formulajs\
Invoke-WebRequest -Uri "https://unpkg.com/formulajs@<version>/dist/formula.js" -OutFile .\assets\vendor\formulajs\formula.js

If only a minified bundle is available, download the upstream source (GitHub release) and build/minify locally; record the build commands and the exact source URL in this README.
