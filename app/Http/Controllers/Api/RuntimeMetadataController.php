<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Northpole\Runtime\Metadata\RuntimeMetadataService;
use Symfony\Component\HttpFoundation\Response;

final class RuntimeMetadataController extends Controller
{
    public function __construct(
        private readonly RuntimeMetadataService $metadata,
    ) {
    }

    public function summary(): JsonResponse
    {
        return response()->json([
            'data' => $this->metadata->summary(),
        ]);
    }

    public function modules(): JsonResponse
    {
        return response()->json([
            'data' => $this->metadata->modules(),
        ]);
    }

    public function module(string $module): JsonResponse
    {
        $metadata = $this->metadata->module($module);

        if ($metadata === null) {
            return response()->json(
                [
                    'message' =>
                        "NorthPole module [{$module}] was not found.",
                ],
                Response::HTTP_NOT_FOUND,
            );
        }

        return response()->json([
            'data' => $metadata,
        ]);
    }

    public function graph(): JsonResponse
    {
        return response()->json([
            'data' => $this->metadata->graph(),
        ]);
    }
}