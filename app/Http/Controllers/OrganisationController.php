<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrganisationRequest;
use App\Http\Resources\OrganisationResource;
use App\Services\OrganisationService;
use Illuminate\Http\JsonResponse;

class OrganisationController extends Controller
{
    public function __construct(
        private readonly OrganisationService $organisationService
    ) {}

    public function store(
        CreateOrganisationRequest $request
    ): JsonResponse {
        $organisation = $this->organisationService->create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Organisation created successfully.',
            'data' => new OrganisationResource($organisation),
        ], 201);
    }
}
