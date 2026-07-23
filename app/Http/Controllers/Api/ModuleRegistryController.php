<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceModule;
use App\Models\OrganisationModule;
use Illuminate\Http\JsonResponse;

class ModuleRegistryController extends Controller
{
    public function index(): JsonResponse
    {
        $modules = MarketplaceModule::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $modules,
        ]);
    }

    public function show(
        MarketplaceModule $marketplaceModule
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $marketplaceModule,
        ]);
    }

    public function install(
        MarketplaceModule $marketplaceModule
    ): JsonResponse {
        $installation = OrganisationModule::withTrashed()
            ->firstOrNew([
                'marketplace_module_id' => $marketplaceModule->id,
            ]);

        if ($installation->trashed()) {
            $installation->restore();
        }

        $installation->fill([
            'is_enabled' => true,
            'installed_at' => now(),
        ]);

        $installation->save();

        return response()->json([
            'success' => true,
            'message' => 'Module installed successfully.',
            'data' => $installation->fresh(),
        ], 201);
    }

    public function disable(
        MarketplaceModule $marketplaceModule
    ): JsonResponse {
        $installation = $this->findTenantInstallation(
            $marketplaceModule
        );

        $installation->update([
            'is_enabled' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Module disabled successfully.',
            'data' => $installation->fresh(),
        ]);
    }

    public function enable(
        MarketplaceModule $marketplaceModule
    ): JsonResponse {
        $installation = $this->findTenantInstallation(
            $marketplaceModule
        );

        $installation->update([
            'is_enabled' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Module enabled successfully.',
            'data' => $installation->fresh(),
        ]);
    }

    public function uninstall(
        MarketplaceModule $marketplaceModule
    ): JsonResponse {
        $installation = $this->findTenantInstallation(
            $marketplaceModule
        );

        $installation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Module uninstalled successfully.',
        ]);
    }

    private function findTenantInstallation(
        MarketplaceModule $marketplaceModule
    ): OrganisationModule {
        return OrganisationModule::query()
            ->where(
                'marketplace_module_id',
                $marketplaceModule->id
            )
            ->firstOrFail();
    }
}