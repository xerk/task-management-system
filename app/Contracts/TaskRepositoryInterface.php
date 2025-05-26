<?php

namespace App\Contracts;

use App\Models\Task;
use App\Models\User;
use App\Enum\TaskStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    /**
     * Find a task by ID.
     */
    public function find(int $id): ?Task;

    /**
     * Create a new task.
     */
    public function create(array $data): Task;

    /**
     * Update an existing task.
     */
    public function update(Task $task, array $data): Task;

    /**
     * Delete a task.
     */
    public function delete(Task $task): bool;

    /**
     * Get paginated tasks with filters.
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get tasks assigned to a user.
     */
    public function getAssignedToUser(User $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get tasks created by a user.
     */
    public function getCreatedByUser(User $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Assign task to user.
     */
    public function assignToUser(Task $task, User $user): Task;

    /**
     * Update task status.
     */
    public function updateStatus(Task $task, TaskStatus $status): Task;

}
