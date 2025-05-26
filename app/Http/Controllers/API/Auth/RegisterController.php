<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Contracts\AuthServiceInterface;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    /**
     * Handle user registration
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully.',
            'data' => [
                'user' => new UserResource($user)
            ]
        ], 201);
    }
}
