<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Northpole\Runtime\Diagnostics\RuntimeDiagnosticsService;

final class RuntimeDiagnosticsController extends Controller
{
    public function __construct(
        private readonly RuntimeDiagnosticsService $diagnostics,
    ) {
    }

    public function show(): JsonResponse
    {
        return response()->json([
            'data' => $this->diagnostics->toArray(),
        ]);
    }
}