<#
build-and-vendor-and-clear.ps1

Builds the project's Tailwind CSS (using package.json scripts), copies the output
into the theme vendor folder, and clears OctoberCMS/October template caches.

Usage:
  # run full flow
  .\build-and-vendor-and-clear.ps1

  # skip npm install
  .\build-and-vendor-and-clear.ps1 -SkipInstall

  # skip Tailwind build/copy (just clear caches)
  .\build-and-vendor-and-clear.ps1 -SkipTailwind
#>

[CmdletBinding()]
param(
    [switch]$SkipInstall,
    [switch]$SkipTailwind
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Write-Log { param($m) Write-Host "[build-and-vendor] $m" }

$scriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Definition
if (-not $scriptRoot) { $scriptRoot = Get-Location }

Write-Log "repoRoot = $scriptRoot"

if (-not $SkipInstall) {
    Write-Log "Running npm install (this may be a no-op if node_modules already present)"
    Push-Location $scriptRoot
    try {
        & npm.cmd install
    } catch {
        Write-Warning "npm install failed: $."
    }
    Pop-Location
}

if (-not $SkipTailwind) {
    Write-Log "Building Tailwind CSS using theme scripts (themes/dataverse)"
    Push-Location $scriptRoot
    try {
        # Prefer running the theme-local npm build so we keep one source of truth
        Write-Log "Running: npm.cmd --prefix themes/dataverse run build"
        & npm.cmd --prefix "themes/dataverse" run build
    } catch {
        Write-Warning "Theme npm build failed: $_. Falling back to a direct PostCSS call against the theme input file."
        try {
            Write-Log "Running: npx.cmd postcss ./themes/dataverse/assets/css/input.css -o ./themes/dataverse/assets/css/output.css"
            & npx.cmd postcss "./themes/dataverse/assets/css/input.css" -o "./themes/dataverse/assets/css/output.css"
        } catch {
            Write-Error "Tailwind build failed: $_"
            Pop-Location
            throw
        }
    }
    Pop-Location

    # copy built CSS into theme vendor (canonical path used by the repo)
    $outputCss = Join-Path $scriptRoot "themes\dataverse\assets\css\output.css"
    $tailwindVendorDir = Join-Path $scriptRoot "themes\dataverse\assets\vendor\tailwind"
    New-Item -ItemType Directory -Force -Path $tailwindVendorDir | Out-Null
    if (Test-Path $outputCss) {
        Copy-Item -Path $outputCss -Destination (Join-Path $tailwindVendorDir "tailwind.css") -Force
        Write-Log "Copied output.css -> $tailwindVendorDir"
    } else {
        Write-Warning "Expected build output not found: $outputCss"
    }
} else {
    Write-Log "Skipping Tailwind build as requested"
}

Write-Log "Clearing OctoberCMS caches (storage folders)"
Write-Log "Removing files under storage/cms/twig, storage/framework/cache, storage/framework/views"
try {
    Remove-Item -Path "$scriptRoot\storage\cms\twig\*" -Recurse -Force -ErrorAction SilentlyContinue
    Remove-Item -Path "$scriptRoot\storage\framework\cache\*" -Recurse -Force -ErrorAction SilentlyContinue
    Remove-Item -Path "$scriptRoot\storage\framework\views\*" -Recurse -Force -ErrorAction SilentlyContinue
} catch {
    Write-Warning ("Cache clear encountered an error: {0}" -f $($Error[0]))
}

Write-Log "Done. If you are running a dev server, reload pages and clear your browser cache (Ctrl+F5)."
