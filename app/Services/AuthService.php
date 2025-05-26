<?php

namespace App\Services;

use App\Models\User;
use App\Contracts\AuthServiceInterface;
use App\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    /**
     * Register a new user
     */
    public function register(array $data): User
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ];

        $user = $this->userRepository->create($userData);

        // Log the user in after registration
        Auth::login($user);

        return $user;
    }

    /**
     * Authenticate user
     */
    public function login(array $credentials): User
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return Auth::user();
    }

    /**
     * Logout user
     */
    public function logout(User $user): void
    {
        Auth::guard('web')->logout();

        // Only handle session if it's available (not in testing with array driver)
        if (request()->hasSession()) {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }
    }
}
