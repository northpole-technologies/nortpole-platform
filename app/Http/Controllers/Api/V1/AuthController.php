<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    /**
     * Register a new user.
     */
    public function register(RegisterUserRequest $request): JsonResponse
    {
        $result = $this->authService->register(
            $request->validated()
        );

        return ApiResponse::success(
            data: $result,
            message: 'Registration successful.',
            status: 201,
            meta: [
                'api_version' => 'v1',
            ]
        );
    }

    /**
     * Login an existing user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->validated()
        );

        if ($result === null) {
            return ApiResponse::error(
                message: 'Invalid email or password.',
                status: 401
            );
        }

        return ApiResponse::success(
            data: $result,
            message: 'Login successful.',
            status: 200,
            meta: [
                'api_version' => 'v1',
            ]
        );
    }
}