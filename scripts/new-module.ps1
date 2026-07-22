param (
    [Parameter(Mandatory = $true)]
    [string]$ModuleName
)

# Resolve backend root from this script's location
$backendRoot = Split-Path -Parent $PSScriptRoot
Set-Location $backendRoot

$ModuleName = $ModuleName.Trim()

if ([string]::IsNullOrWhiteSpace($ModuleName)) {
    Write-Host "Module name cannot be empty." -ForegroundColor Red
    exit 1
}

if ($ModuleName -notmatch '^[A-Za-z][A-Za-z0-9]*$') {
    Write-Host "Module name must begin with a letter and contain only letters and numbers." -ForegroundColor Red
    exit 1
}

$modulePath = Join-Path $backendRoot "modules\$ModuleName"

if (Test-Path $modulePath) {
    Write-Host "Module already exists: $modulePath" -ForegroundColor Red
    exit 1
}

$moduleSlug = ($ModuleName -creplace '([a-z0-9])([A-Z])', '$1-$2').ToLower()
$moduleNamespace = "Modules\$ModuleName"

$folders = @(
    $modulePath,
    "$modulePath\Application",
    "$modulePath\Application\Actions",
    "$modulePath\Application\DTOs",
    "$modulePath\Application\Interfaces",
    "$modulePath\Application\Services",
    "$modulePath\Config",
    "$modulePath\Database",
    "$modulePath\Database\Migrations",
    "$modulePath\Database\Seeders",
    "$modulePath\Domain",
    "$modulePath\Domain\Entities",
    "$modulePath\Domain\Models",
    "$modulePath\Domain\Repositories",
    "$modulePath\Domain\ValueObjects",
    "$modulePath\Http",
    "$modulePath\Http\Controllers",
    "$modulePath\Http\Requests",
    "$modulePath\Http\Resources",
    "$modulePath\Infrastructure",
    "$modulePath\Infrastructure\Providers",
    "$modulePath\Infrastructure\Repositories",
    "$modulePath\Resources",
    "$modulePath\Resources\Lang",
    "$modulePath\Resources\Views",
    "$modulePath\Routes",
    "$modulePath\Tests",
    "$modulePath\Tests\Feature",
    "$modulePath\Tests\Unit"
)

foreach ($folder in $folders) {
    New-Item -ItemType Directory -Path $folder -Force | Out-Null
    Write-Host "Created folder: $folder" -ForegroundColor Green
}

$moduleJson = @"
{
    "name": "$ModuleName",
    "slug": "$moduleSlug",
    "version": "0.1.0",
    "description": "$ModuleName module for the NorthPole Platform",
    "enabled": true
}
"@

$controller = @"
<?php

namespace $moduleNamespace\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ${ModuleName}Controller extends Controller
{
    public function index(): View
    {
        return view('$moduleSlug::index');
    }

    public function status(): JsonResponse
    {
        return response()->json([
            'module' => '$ModuleName',
            'status' => 'active',
            'version' => '0.1.0',
        ]);
    }
}
"@

$apiRoutes = @"
<?php

use Illuminate\Support\Facades\Route;
use $moduleNamespace\Http\Controllers\${ModuleName}Controller;

Route::get('/$moduleSlug/status', [${ModuleName}Controller::class, 'status'])
    ->name('$moduleSlug.api.status');
"@

$webRoutes = @"
<?php

use Illuminate\Support\Facades\Route;
use $moduleNamespace\Http\Controllers\${ModuleName}Controller;

Route::get('/$moduleSlug', [${ModuleName}Controller::class, 'index'])
    ->name('$moduleSlug.home');
"@

$view = @"
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$ModuleName</title>
</head>
<body>
    <h1>$ModuleName</h1>
    <p>The $ModuleName module is active.</p>
</body>
</html>
"@

$files = @{
    "$modulePath\module.json" = $moduleJson
    "$modulePath\Http\Controllers\${ModuleName}Controller.php" = $controller
    "$modulePath\Routes\api.php" = $apiRoutes
    "$modulePath\Routes\web.php" = $webRoutes
    "$modulePath\Resources\Views\index.blade.php" = $view
}

$utf8NoBom = New-Object System.Text.UTF8Encoding($false)

foreach ($file in $files.GetEnumerator()) {
    [System.IO.File]::WriteAllText(
        $file.Key,
        $file.Value,
        $utf8NoBom
    )

    Write-Host "Created file: $($file.Key)" -ForegroundColor Cyan
}

Write-Host ""
Write-Host "$ModuleName module created successfully." -ForegroundColor Green
Write-Host "Path: $modulePath"
Write-Host "Slug: $moduleSlug"