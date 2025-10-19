# Clear all OctoberCMS caches from any directory
# Works no matter where you call it from

# --- CONFIG ---
$basePath = "C:\laragon\www\active\octobertest2"

# --- FUNCTIONS ---
function Run-Artisan($command) {
    Write-Host ("Running: php artisan " + $command) -ForegroundColor Cyan
    & php artisan $command
    if ($LASTEXITCODE -ne 0) {
        Write-Host ("Failed: php artisan " + $command) -ForegroundColor Red
    } else {
        Write-Host ("Completed: " + $command) -ForegroundColor Green
    }
}

# --- MAIN ---
Set-Location $basePath

Write-Host "============================================"
Write-Host " Clearing OctoberCMS caches (DataVerse)"
Write-Host (" Location: " + $basePath)
Write-Host "============================================"

# Laravel / October cache clears
Run-Artisan "cache:clear"
Run-Artisan "config:clear"
Run-Artisan "route:clear"
Run-Artisan "view:clear"
#Run-Artisan "twig:clear"

# Manual cleanups
if (Test-Path ($basePath + "\storage\framework\cache")) {
    Remove-Item ($basePath + "\storage\framework\cache") -Recurse -Force -ErrorAction SilentlyContinue
    Write-Host "storage/framework/cache cleared manually" -ForegroundColor Yellow
}

if (Test-Path ($basePath + "\storage\temp")) {
    Remove-Item ($basePath + "\storage\temp\*") -Recurse -Force -ErrorAction SilentlyContinue
    Write-Host "storage/temp cleared manually" -ForegroundColor Yellow
}

Write-Host "============================================"
Write-Host "All caches purged successfully."
Write-Host "============================================"