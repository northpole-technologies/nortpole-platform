# ==========================================
# SantaBuddy HTTP Structure Builder
# Run from the Laravel backend root
# ==========================================

$folders = @(
    "modules\SantaBuddy\Http",
    "modules\SantaBuddy\Http\Controllers",
    "modules\SantaBuddy\Http\Requests",
    "modules\SantaBuddy\Http\Resources"
)

$files = @(
    "modules\SantaBuddy\Http\Controllers\SantaBuddyController.php"
)

foreach ($folder in $folders) {
    if (-not (Test-Path $folder)) {
        New-Item -ItemType Directory -Path $folder -Force | Out-Null
        Write-Host "Created folder: $folder" -ForegroundColor Green
    }
    else {
        Write-Host "Folder exists:  $folder" -ForegroundColor Yellow
    }
}

foreach ($file in $files) {
    if (-not (Test-Path $file)) {
        New-Item -ItemType File -Path $file -Force | Out-Null
        Write-Host "Created file:   $file" -ForegroundColor Green
    }
    else {
        Write-Host "File exists:    $file" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "SantaBuddy HTTP structure is ready." -ForegroundColor Cyan