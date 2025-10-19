param(
    [Parameter(ValueFromRemainingArguments=$true)]
    [string[]]$ComposerArgs
)

# `composer.ps1` - runs vendored composer.phar using the project's PHP runtime.
# Usage: .\composer.ps1 install

$scriptRoot = $PSScriptRoot
# wrappers -> vendor\tools -> vendor -> repo root -> go up three levels from wrappers
$repoRoot = Resolve-Path (Join-Path $scriptRoot '..\..\..') | Select-Object -ExpandProperty Path
$pharPath = Join-Path $repoRoot 'vendor\tools\composer\composer.phar'

if (-not (Test-Path $pharPath)) {
    Write-Error "Vendored composer not found at $pharPath"
    exit 1
}

# Preferred: use system PHP if in PATH; otherwise fallback to common Laragon path
try {
    $phpCmd = Get-Command php -ErrorAction SilentlyContinue
    if ($phpCmd) { $phpExe = $phpCmd.Source }
    else { $phpExe = $null }
}
catch {
    $phpExe = $null
}
if (-not $phpExe) {
    $defaultLaragonPhp = 'C:\\laragon\\bin\\php\\php-8.4.13-Win32-vs17-x64\\php.exe'
    if (Test-Path $defaultLaragonPhp) { $phpExe = $defaultLaragonPhp }
}

if (-not $phpExe) {
    Write-Error "No PHP executable found. Please install PHP or update the wrapper to point to your PHP binary."
    exit 1
}

& $phpExe $pharPath @ComposerArgs
