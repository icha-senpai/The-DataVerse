# Clear all OctoberCMS caches from any directory
# Works no matter where you call it from

# Resolve base path dynamically
$basePath = "C:\laragon\www\active\octobertest2"

# Function to run Artisan command cleanly
function Run-Artisan($command) {
    Write-Host "▶ Running: php artisan $command" -ForegroundColor Cyan
    & php artisan $command
    if ($LASTEXITCODE -ne 0) {
        Write-Host "⚠ Failed: php artisan $command" -ForegroundColor Red
    } else {
        Write-Host "✔ Completed: $command" -ForegroundColor Green
    }
}

# Move into the project directory
Set-Location $basePath

Write-Host "============================================"
Write-Host "   🔥 Clearing OctoberCMS caches (DataVerse) 🔥"
Write-Host "   Location: $basePath"
Write-Host "============================================"

# Core Laravel/October cache clears
Run-Artisan "cache:clear"
Run-Artisan "config:clear"
Run-Artisan "route:clear"
Run-Artisan "view:clear"
Run-Artisan "twig:clear"

# Clear compiled classes (if exist)
if (Test-Path "$basePath/storage/framework/cache") {
    Remove-Item "$basePath/storage/framework/cache" -Recurse -Force -ErrorAction SilentlyContinue
    Write-Host "🧹 storage/framework/cache cleared manually" -ForegroundColor Yellow
}

# Clear October-specific temp files
$octoberTemp = "$basePath/storage/temp"
if (Test-Path $octoberTemp) {
    Remove-Item "$octoberTemp\*" -Recurse -Force -ErrorAction SilentlyContinue
    Write-Host "🗑 storage/temp cleaned" -ForegroundColor Yellow
}

Write-Host "============================================"
Write-Host "✅ All caches purged successfully."
Write-Host "============================================"
Write-Host "👍 Have a great day!" -ForegroundColor Green