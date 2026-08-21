Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$scriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Definition
if (-not $scriptRoot) { $scriptRoot = Get-Location }

function Invoke-Artisan {
    param([string]$Command)

    Write-Host "Running: php artisan $Command" -ForegroundColor Cyan
    & php artisan $Command

    if ($LASTEXITCODE -ne 0) {
        throw "Artisan command failed: php artisan $Command"
    }
}

Push-Location $scriptRoot
try {
    if (-not (Test-Path (Join-Path $scriptRoot 'artisan'))) {
        throw "Could not find artisan at $scriptRoot"
    }

    Write-Host "Clearing Laravel and October caches for: $scriptRoot" -ForegroundColor Cyan

    Invoke-Artisan 'cache:clear'
    Invoke-Artisan 'config:clear'
    Invoke-Artisan 'route:clear'
    Invoke-Artisan 'view:clear'
}
finally {
    Pop-Location
}

Write-Host "Clearing OctoberCMS cache folders under: $scriptRoot\storage" -ForegroundColor Cyan

$targets = @(
    "$scriptRoot\storage\cms\twig\*",
    "$scriptRoot\storage\framework\cache\*",
    "$scriptRoot\storage\framework\views\*",
    "$scriptRoot\storage\temp\*"
)

foreach ($t in $targets) {
    Write-Host "Removing -> $t"
    try {
        Remove-Item -Path $t -Recurse -Force -ErrorAction SilentlyContinue
    } catch {
        Write-Warning ("Failed to remove {0}: {1}" -f $t, $($Error[0]))
    }
}

Write-Host "Cache clear complete." -ForegroundColor Green
