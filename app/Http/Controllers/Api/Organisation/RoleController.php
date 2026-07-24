<?php

namespace App\Http\Controllers\Api\Organisation;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::query()
            ->with('permissions')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }

    public function store(
        Request $request,
        TenantContext $tenantContext
    ): JsonResponse {
        $validated = $request->validate([
            'key' => [
                'required',
                'string',
                'max:100',
                'alpha_dash:ascii',
                Rule::unique('roles', 'key')
                    ->where(
                        fn ($query) => $query->where(
                            'organisation_id',
                            $tenantContext->organisationId()
                        )
                    ),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $role = Role::create([
            'key' => $validated['key'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_system' => false,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully.',
            'data' => $role,
        ], Response::HTTP_CREATED);
    }

    public function show(int $role): JsonResponse
    {
        $resolvedRole = Role::query()
            ->with('permissions')
            ->findOrFail($role);

        return response()->json([
            'success' => true,
            'data' => $resolvedRole,
        ]);
    }

    public function update(
        Request $request,
        TenantContext $tenantContext,
        int $role
    ): JsonResponse {
        $resolvedRole = Role::query()
            ->findOrFail($role);

        $validated = $request->validate([
            'key' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                'alpha_dash:ascii',
                Rule::unique('roles', 'key')
                    ->ignore($resolvedRole->id)
                    ->where(
                        fn ($query) => $query->where(
                            'organisation_id',
                            $tenantContext->organisationId()
                        )
                    ),
            ],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:2000',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ]);

        if (
            $resolvedRole->is_system
            && array_key_exists('key', $validated)
            && $validated['key'] !== $resolvedRole->key
        ) {
            return response()->json([
                'success' => false,
                'message' => 'System role keys cannot be changed.',
            ], Response::HTTP_CONFLICT);
        }

        $resolvedRole->fill($validated);
        $resolvedRole->save();
        $resolvedRole->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully.',
            'data' => $resolvedRole,
        ]);
    }

    public function syncPermissions(
        Request $request,
        int $role
    ): JsonResponse {
        $resolvedRole = Role::query()
            ->findOrFail($role);

        $validated = $request->validate([
            'permissions' => [
                'present',
                'array',
            ],
            'permissions.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('permissions', 'id')
                    ->where(
                        fn ($query) => $query->where(
                            'is_active',
                            true
                        )
                    ),
            ],
        ]);

        $resolvedRole->permissions()->sync(
            $validated['permissions']
        );

        $resolvedRole->load([
            'permissions' => fn ($query) => $query
                ->orderBy('module_key')
                ->orderBy('name'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permissions updated successfully.',
            'data' => $resolvedRole,
        ]);
    }

    public function destroy(int $role): JsonResponse
    {
        $resolvedRole = Role::query()
            ->findOrFail($role);

        if ($resolvedRole->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'System roles cannot be deleted.',
            ], Response::HTTP_CONFLICT);
        }

        if ($resolvedRole->users()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Roles assigned to users cannot be deleted.',
            ], Response::HTTP_CONFLICT);
        }

        $resolvedRole->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully.',
        ]);
    }
}