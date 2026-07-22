# ==========================================
# Fix SantaBuddy PSR-4 Folder Structure
# ==========================================

$source = ".\modules\SantaBuddy\src"
$target = ".\modules\SantaBuddy"

if (-not (Test-Path $source)) {
    Write-Host "SantaBuddy src folder not found." -ForegroundColor Red
    exit 1
}

Get-ChildItem $source -Force | ForEach-Object {
    $destination = Join-Path $target $_.Name

    if (Test-Path $destination) {
        Write-Host "Already exists, not overwritten: $destination" -ForegroundColor Yellow
    }
    else {
        Move-Item $_.FullName $destination
        Write-Host "Moved: $($_.Name)" -ForegroundColor Green
    }
}

$remainingItems = Get-ChildItem $source -Force

if ($remainingItems.Count -eq 0) {
    Remove-Item $source -Force
    Write-Host "Removed empty src folder." -ForegroundColor Cyan
}
else {
    Write-Host ""
    Write-Host "The src folder still contains files:" -ForegroundColor Yellow
    $remainingItems | Select-Object Name, FullName
    Write-Host ""
    Write-Host "Nothing remaining was overwritten." -ForegroundColor Yellow
}