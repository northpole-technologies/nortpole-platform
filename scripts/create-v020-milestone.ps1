$backendRoot = Split-Path -Parent $PSScriptRoot

$releaseFolder = Join-Path $backendRoot "docs\ReleaseNotes"
$architectureFolder = Join-Path $backendRoot "docs\Architecture"

New-Item -ItemType Directory -Path $releaseFolder -Force | Out-Null
New-Item -ItemType Directory -Path $architectureFolder -Force | Out-Null

$releaseFile = Join-Path $releaseFolder "v0.2.0.md"
$architectureFile = Join-Path $architectureFolder "003-Dynamic-Module-System.md"

$releaseLines = @(
    '# NorthPole Platform v0.2.0',
    '',
    '## Release Date',
    '',
    '22 July 2026',
    '',
    '## Overview',
    '',
    'This release introduces the first dynamic module system for the NorthPole Platform.',
    '',
    '## Completed',
    '',
    '- Dynamic module generator',
    '- Automatic module discovery',
    '- Automatic web route loading',
    '- Automatic API route loading',
    '- PSR-4 namespace integration',
    '- SantaBuddy controller migration',
    '- HomeDoctor module generation',
    '',
    '## Current Modules',
    '',
    '- SantaBuddy',
    '- HomeDoctor',
    '',
    '## Known Issues',
    '',
    '- Automatic module view registration is being completed',
    '- Configuration loading is pending',
    '- Migration loading is pending',
    '- Provider registration is pending',
    '',
    '## Next Version',
    '',
    'Version 0.3.0 will focus on module resources, configuration, migrations, providers and improved developer tooling.'
)

$architectureLines = @(
    '# Dynamic Module System',
    '',
    '## Objective',
    '',
    'Allow applications to be created and installed as independent modules without hardcoding each module into Laravel.',
    '',
    '## Discovery Flow',
    '',
    '1. Scan the modules directory.',
    '2. Read module.json.',
    '3. Validate the module manifest.',
    '4. Check that the module is enabled.',
    '5. Load web and API routes.',
    '6. Load module resources and providers.',
    '7. Make the module available to the application.',
    '',
    '## Current Components',
    '',
    '- Platform\Modules\ModuleManager',
    '- Platform\Modules\ModuleRouteLoader',
    '- Platform\Modules\ModuleResourceLoader',
    '',
    '## Module Generator',
    '',
    'Command:',
    '',
    '    .\scripts\new-module.ps1 HomeDoctor',
    '',
    '## Namespace Rules',
    '',
    'Platform namespace maps to the platform directory.',
    'Modules namespace maps to the modules directory.',
    '',
    'Example class:',
    '',
    '    Modules\HomeDoctor\Http\Controllers\HomeDoctorController',
    '',
    'Required file location:',
    '',
    '    modules\HomeDoctor\Http\Controllers\HomeDoctorController.php',
    '',
    '## Planned Components',
    '',
    '- ModuleConfigLoader',
    '- ModuleMigrationLoader',
    '- ModuleProviderLoader',
    '- ModuleValidator',
    '- NorthPole CLI'
)

$utf8NoBom = New-Object System.Text.UTF8Encoding($false)

[System.IO.File]::WriteAllLines($releaseFile, $releaseLines, $utf8NoBom)
[System.IO.File]::WriteAllLines($architectureFile, $architectureLines, $utf8NoBom)

Write-Host ""
Write-Host "NorthPole v0.2.0 milestone documents created." -ForegroundColor Green
Write-Host "Release notes: $releaseFile" -ForegroundColor Cyan
Write-Host "Architecture:  $architectureFile" -ForegroundColor Cyan