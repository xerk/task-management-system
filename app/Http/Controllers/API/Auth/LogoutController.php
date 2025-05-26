<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Contracts\AuthServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    /**
     * Handle user logout
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
            'data' => null
        ]);
    }
}
