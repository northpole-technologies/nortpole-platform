# ==========================================
# Northpole Platform Bootstrap
# Version: 0.1.0
# ==========================================

$folders = @(
    # Modules
    "modules",
    "modules\SantaBuddy",

    # Platform
    "platform",
    "Northpole\Core",
    "Northpole\Contracts",
    "Northpole\Registry",
    "Northpole\Loader",
    "Northpole\Services",
    "Northpole\Support",
    "Northpole\Exceptions",

    # Documentation
    "docs",
    "docs\Architecture",
    "docs\API",
    "docs\Marketplace",
    "docs\Development",
    "docs\ReleaseNotes",

    # Scripts
    "scripts"
)

$created = 0
$existing = 0

foreach ($folder in $folders) {

    if (Test-Path $folder) {
        Write-Host "[EXISTS]  $folder" -ForegroundColor Yellow
        $existing++
    }
    else {
        New-Item -ItemType Directory -Force -Path $folder | Out-Null
        Write-Host "[CREATED] $folder" -ForegroundColor Green
        $created++
    }

}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "Northpole Bootstrap Complete" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "Created : $created"
Write-Host "Existing: $existing"
Write-Host ""

tree . /A