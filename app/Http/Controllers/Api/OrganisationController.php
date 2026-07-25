<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrganisationRequest;
use App\Http\Requests\UpdateOrganisationRequest;
use App\Models\Organisation;
use Illuminate\Http\JsonResponse;

class OrganisationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Organisation::latest()->paginate(10)
        );
    }

    public function show(Organisation $organisation): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $organisation,
        ]);
    }

    public function store(CreateOrganisationRequest $request): JsonResponse
    {
        $organisation = Organisation::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Organisation created successfully.',
            'data' => $organisation,
        ], 201);
    }

    public function update(
        UpdateOrganisationRequest $request,
        Organisation $organisation
    ): JsonResponse {
        $organisation->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Organisation updated successfully.',
            'data' => $organisation->fresh(),
        ]);
    }

    public function destroy(Organisation $organisation): JsonResponse
    {
        $organisation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Organisation deleted successfully.',
        ]);
    }
}
