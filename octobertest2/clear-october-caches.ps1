<#
clear-october-caches.ps1

Clears OctoberCMS cache folders under storage/ so templates and cached views are regenerated.

Usage:
  .\clear-october-caches.ps1
  # or to run from anywhere:
  powershell -NoProfile -ExecutionPolicy Bypass -File .\clear-october-caches.ps1
#>

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$scriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Definition
if (-not $scriptRoot) { $scriptRoot = Get-Location }

Write-Host "Clearing OctoberCMS caches under: $scriptRoot\storage" -ForegroundColor Cyan

$targets = @(
    "$scriptRoot\storage\cms\twig\*",
    "$scriptRoot\storage\framework\cache\*",
    "$scriptRoot\storage\framework\views\*",
    "$scriptRoot\storage\logs\*"
)

foreach ($t in $targets) {
    Write-Host "Removing -> $t"
    try {
        Remove-Item -Path $t -Recurse -Force -ErrorAction SilentlyContinue
    } catch {
        Write-Warning ("Failed to remove {0}: {1}" -f $t, $($Error[0]))
    }
}

Write-Host "Cache clear complete. Restart any dev server and hard-refresh your browser (Ctrl+F5)." -ForegroundColor Green
