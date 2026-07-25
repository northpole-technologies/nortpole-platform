<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceModule;
use Illuminate\Http\JsonResponse;
use Northpole\Lifecycle\ModuleLifecycleManager;

class ModuleRegistryController extends Controller
{
    public function __construct(
        private readonly ModuleLifecycleManager $lifecycleManager
    ) {}

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
        $installation = $this->lifecycleManager->install(
            $marketplaceModule
        );

        return response()->json([
            'success' => true,
            'message' => 'Module installed successfully.',
            'data' => $installation,
        ], 201);
    }

    public function disable(
        MarketplaceModule $marketplaceModule
    ): JsonResponse {
        $installation = $this->lifecycleManager->disable(
            $marketplaceModule
        );

        return response()->json([
            'success' => true,
            'message' => 'Module disabled successfully.',
            'data' => $installation,
        ]);
    }

    public function enable(
        MarketplaceModule $marketplaceModule
    ): JsonResponse {
        $installation = $this->lifecycleManager->enable(
            $marketplaceModule
        );

        return response()->json([
            'success' => true,
            'message' => 'Module enabled successfully.',
            'data' => $installation,
        ]);
    }

    public function uninstall(
        MarketplaceModule $marketplaceModule
    ): JsonResponse {
        $this->lifecycleManager->uninstall(
            $marketplaceModule
        );

        return response()->json([
            'success' => true,
            'message' => 'Module uninstalled successfully.',
        ]);
    }
}
