# Find the nearest directory containing "artisan"
function Find-ArtisanRoot {
    param([string]$StartDir)

    $dir = Resolve-Path $StartDir
    while ($dir -and (Test-Path $dir)) {
        if (Test-Path (Join-Path $dir "artisan")) {
            return $dir
        }
        $parent = Split-Path $dir -Parent
        if ($parent -eq $dir) { break }  # stop if we hit drive root
        $dir = $parent
    }

    throw "Could not find an 'artisan' file above $StartDir"
}

# Remember where we started
$Current = Get-Location

try {
    # Locate OctoberCMS root (where artisan lives)
    $Root = Find-ArtisanRoot $Current

    Write-Host "Found October root: $Root" -ForegroundColor Cyan

    # Move there, run command
    Set-Location $Root
    php artisan dataverse:rebuild-nav

} finally {
    # Return to your original directory
    Set-Location $Current
}
