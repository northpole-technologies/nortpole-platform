<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    public function handle(
        Request $request,
        Closure $next,
        string $permission
    ): Response {
        $user = $request->user();

        if (
            ! $user instanceof User
            || ! $user->hasPermission($permission)
        ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'You do not have permission to perform this action.',
                'required_permission' => $permission,
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
