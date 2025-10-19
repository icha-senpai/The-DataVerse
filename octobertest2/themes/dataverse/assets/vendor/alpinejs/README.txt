Vendored AlpineJS
-----------------

This folder should contain a copy of Alpine.js v3 (CDN build) for offline and provenance reasons.

Recommended vendor file: alpine.cdn.min.js from jsDelivr / unpkg for a specific version.
Example (PowerShell) to fetch v3.14.9:

New-Item -ItemType Directory -Force -Path .\themes\dataverse\assets\vendor\alpinejs
Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js" -OutFile .\themes\dataverse\assets\vendor\alpinejs\alpine.cdn.min.js -UseBasicParsing

Record the source URL and license next to the file after vendoring.

If you prefer npm (fetch + copy):

npm.cmd install alpinejs@3.14.9 --no-save
Copy-Item -Path .\node_modules\alpinejs\dist\cdn.min.js -Destination .\themes\dataverse\assets\vendor\alpinejs\alpine.cdn.min.js -Force

Verifying integrity (optional):
- After vendoring, record the SHA256 checksum to detect accidental changes:
	(Get-FileHash .\themes\dataverse\assets\vendor\alpinejs\alpine.cdn.min.js -Algorithm SHA256).Hash

Note: Automated downloads from this environment stalled; please run one of the above commands locally in your dev machine to fetch the official build. Once you copy the real file in place, remove the placeholder and commit the vendored file with a short README entry documenting the source and license.
Vendored: 2025-10-14T15:16:50.6775452-05:00\nSHA256: 3ED1EED252488921DF65E363D6715DEB04D7F92AAEDB9E52199FDF73CB1E0AD3
