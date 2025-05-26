<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Contracts\AuthServiceInterface;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    /**
     * Handle user login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->login($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => new UserResource($user)
            ]
        ]);
    }
}
