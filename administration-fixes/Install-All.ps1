$ErrorActionPreference = 'Stop'
function Get-TaskSha256([string]$Path) {
    $stream = [IO.File]::OpenRead($Path)
    $algorithm = [Security.Cryptography.SHA256]::Create()
    try { return [BitConverter]::ToString($algorithm.ComputeHash($stream)).Replace('-', '').ToLowerInvariant() }
    finally { $stream.Dispose(); $algorithm.Dispose() }
}
$projectRoot = [IO.Path]::GetFullPath((Split-Path -Parent $PSScriptRoot))
$projectPrefix = $projectRoot.TrimEnd('\') + '\'
$manifest = Get-Content -LiteralPath (Join-Path $PSScriptRoot 'manifest.json') -Raw | ConvertFrom-Json
$prepared = @()
foreach ($entry in $manifest) {
    $source = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot $entry.file))
    $destination = [IO.Path]::GetFullPath((Join-Path $projectRoot $entry.destination))
    if (!$destination.StartsWith($projectPrefix, [StringComparison]::OrdinalIgnoreCase)) { throw 'A destination is outside the project.' }
    if ((Get-TaskSha256 $source) -ne $entry.sha256) { throw "Replacement file was modified: $($entry.file)" }
    if (Test-Path -LiteralPath $destination) {
        $currentHash = Get-TaskSha256 $destination
        if ($currentHash -ne $entry.sha256 -and $currentHash -ne $entry.original_sha256) { throw "This project file changed after this set was prepared: $destination. Preserve your newer file before installing." }
    } elseif (!$entry.new) { throw "Expected existing file is missing: $destination" }
    $prepared += [PSCustomObject]@{ Source=$source; Destination=$destination; Relative=$entry.destination }
}
$backupRoot = Join-Path $PSScriptRoot ('_installation-backups\' + (Get-Date -Format 'yyyyMMdd-HHmmss') + '-' + [Guid]::NewGuid().ToString('N').Substring(0,8))
New-Item -ItemType Directory -Path $backupRoot -Force | Out-Null
# Back up every existing target before replacing any file.
foreach ($item in $prepared) {
    if (Test-Path -LiteralPath $item.Destination) {
        $backupPath = Join-Path $backupRoot $item.Relative
        New-Item -ItemType Directory -Path (Split-Path -Parent $backupPath) -Force | Out-Null
        Copy-Item -LiteralPath $item.Destination -Destination $backupPath
    }
}
# Bootstrap and route registration go last, after all their dependencies exist.
foreach ($item in ($prepared | Sort-Object @{Expression={if ($_.Relative -eq 'bootstrap/app.php' -or $_.Relative -eq 'routes/web.php') {1} else {0}}})) {
    New-Item -ItemType Directory -Path (Split-Path -Parent $item.Destination) -Force | Out-Null
    Copy-Item -LiteralPath $item.Source -Destination $item.Destination -Force
}
Write-Host "Installed $($prepared.Count) files. Original files backed up in $backupRoot"
Write-Host 'Next: C:\xampp\php\php.exe artisan optimize:clear'
Write-Host 'Then restart php artisan serve and any queue worker, and refresh with Ctrl+F5.'
