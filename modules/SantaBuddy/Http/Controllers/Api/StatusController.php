<?php

namespace Modules\SantaBuddy\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class StatusController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'module' => 'SantaBuddy',
            'status' => 'running',
            'version' => '1.0.0',
        ]);
    }
}
