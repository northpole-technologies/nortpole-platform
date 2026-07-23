<?php

namespace App\Http\Middleware;

use App\Models\Organisation;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $organisationId = $request->header('X-Organisation-Id');

        if (! is_string($organisationId) || trim($organisationId) === '') {
            return new JsonResponse([
                'success' => false,
                'message' => 'The X-Organisation-Id header is required.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $request->user();

        if ($user === null) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $organisation = Organisation::query()
            ->whereKey($organisationId)
            ->where('active', true)
            ->first();

        if (
            $organisation === null
            || ! $user->belongsToOrganisation($organisation)
        ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Organisation access was not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->tenantContext->set($organisation);

        try {
            return $next($request);
        } finally {
            $this->tenantContext->clear();
        }
    }
}