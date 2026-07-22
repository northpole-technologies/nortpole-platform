<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrganisationRequest;
use App\Models\Organisation;
use Illuminate\Http\JsonResponse;

class OrganisationController extends Controller
{
    public function store(CreateOrganisationRequest $request): JsonResponse
    {
        $organisation = Organisation::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Organisation created successfully.',
            'data' => $organisation,
        ], 201);
    }
}