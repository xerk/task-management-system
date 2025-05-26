<?php

namespace App\Repositories;

use App\Models\User;
use App\Contracts\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Create a new user
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Find user by ID
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Update user
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    /**
     * Delete user
     *
     * @param User $user
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }
}
