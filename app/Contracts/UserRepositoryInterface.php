<?php

namespace App\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Create a new user
     */
    public function create(array $data): User;

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find user by ID
     */
    public function findById(int $id): ?User;

    /**
     * Update user
     */
    public function update(User $user, array $data): User;

    /**
     * Delete user
     */
    public function delete(User $user): bool;
}
