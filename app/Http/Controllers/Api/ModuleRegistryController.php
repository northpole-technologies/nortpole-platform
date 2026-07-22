<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceModule;
use App\Models\Organisation;
use App\Models\OrganisationModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        Request $request,
        MarketplaceModule $marketplaceModule
    ): JsonResponse {
        $validated = $request->validate([
            'organisation_id' => [
                'required',
                'exists:organisations,id',
            ],
        ]);

        $organisation = Organisation::findOrFail(
            $validated['organisation_id']
        );

        $installation = OrganisationModule::updateOrCreate(
            [
                'organisation_id' => $organisation->id,
                'marketplace_module_id' => $marketplaceModule->id,
            ],
            [
                'is_enabled' => true,
                'installed_at' => now(),
                'deleted_at' => null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Module installed successfully.',
            'data' => $installation,
        ], 201);
    }

    public function disable(
        Request $request,
        MarketplaceModule $marketplaceModule
    ): JsonResponse {
        $validated = $request->validate([
            'organisation_id' => [
                'required',
                'exists:organisations,id',
            ],
        ]);

        $installation = OrganisationModule::query()
            ->where('organisation_id', $validated['organisation_id'])
            ->where('marketplace_module_id', $marketplaceModule->id)
            ->firstOrFail();

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
        Request $request,
        MarketplaceModule $marketplaceModule
    ): JsonResponse {
        $validated = $request->validate([
            'organisation_id' => [
                'required',
                'exists:organisations,id',
            ],
        ]);

        $installation = OrganisationModule::query()
            ->where('organisation_id', $validated['organisation_id'])
            ->where('marketplace_module_id', $marketplaceModule->id)
            ->firstOrFail();

        $installation->update([
            'is_enabled' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Module enabled successfully.',
            'data' => $installation->fresh(),
        ]);
    }
}