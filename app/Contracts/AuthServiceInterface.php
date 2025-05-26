<?php

namespace App\Contracts;

use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Register a new user
     */
    public function register(array $data): User;

    /**
     * Authenticate user
     */
    public function login(array $credentials): User;

    /**
     * Logout user
     */
    public function logout(User $user): void;
}
