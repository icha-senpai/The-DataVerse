Tabulator vendored files
Source: https://unpkg.com/tabulator-tables/
Files:
Files included (in this folder):
- tabulator.js
- tabulator.css
Version: latest from unpkg at time of download
License: MIT (see upstream)

Policy: DO NOT download or commit minified files (e.g., *.min.js, *.min.css) directly from CDNs without also vendoring the unminified source and recording provenance.

Recommended workflow:
1) Prefer the unminified/dist JS and CSS from the upstream package. Download the full (non-minified) builds when available and keep a record of the source URL + tag/commit.
2) If the upstream only publishes a prebuilt minified bundle, document the upstream tarball/zip URL, version/tag, and license in this README and vendor that tarball alongside a README with provenance.
3) If you need a minified file for production, create it locally (in `dev/`) and record the build/minify command and input hash in this README.

Why: Unminified sources are auditable, can include source maps and comments, and are reproducible. Minified files from CDNs lack provenance and make security reviews harder.

PowerShell example — download non-minified (preferred):

New-Item -ItemType Directory -Force -Path .\assets\vendor\tabulator\
Invoke-WebRequest -Uri "https://unpkg.com/tabulator-tables@6.3.1/dist/js/tabulator.js" -OutFile .\assets\vendor\tabulator\tabulator.js
Invoke-WebRequest -Uri "https://unpkg.com/tabulator-tables@6.3.1/dist/css/tabulator.css" -OutFile .\assets\vendor\tabulator\tabulator.css

PowerShell example — if you must create a minified file locally:

cd .\dev\
npm.cmd install
# build the library (or use a minifier) and copy resulting files into ../assets/vendor/tabulator/
Copy-Item -Path .\dist\tabulator.js -Destination ..\assets\vendor\tabulator\tabulator.js -Force
Copy-Item -Path .\dist\tabulator.css -Destination ..\assets\vendor\tabulator\tabulator.css -Force

Add the exact source URL and build command to this README after performing the build.
