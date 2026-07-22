# Dynamic Module System

## Objective

Allow applications to be created and installed as independent modules without hardcoding each module into Laravel.

## Discovery Flow

1. Scan the modules directory.
2. Read module.json.
3. Validate the module manifest.
4. Check that the module is enabled.
5. Load web and API routes.
6. Load module resources and providers.
7. Make the module available to the application.

## Current Components

- Platform\Modules\ModuleManager
- Platform\Modules\ModuleRouteLoader
- Platform\Modules\ModuleResourceLoader

## Module Generator

Command:

    .\scripts\new-module.ps1 HomeDoctor

## Namespace Rules

Platform namespace maps to the platform directory.
Modules namespace maps to the modules directory.

Example class:

    Modules\HomeDoctor\Http\Controllers\HomeDoctorController

Required file location:

    modules\HomeDoctor\Http\Controllers\HomeDoctorController.php

## Planned Components

- ModuleConfigLoader
- ModuleMigrationLoader
- ModuleProviderLoader
- ModuleValidator
- NorthPole CLI
