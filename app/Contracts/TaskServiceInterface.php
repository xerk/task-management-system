<?php

namespace App\Contracts;

use App\Models\Task;
use Illuminate\Support\Collection;

interface TaskServiceInterface
{
    /**
     * Create a new task.
     */
    public function createTask(array $data): Task;

    /**
     * Update an existing task.
     */
    public function updateTask(Task $task, array $data): Task;

    /**
     * Delete a task.
     */
    public function deleteTask(Task $task): bool;

    /**
     * Get a task by ID.
     */
    public function getTaskById(int $id): Task;

    /**
     * Get all tasks.
     */
    public function getAllTasks(): Collection;

    /**
     * Get all tasks by user ID.
     */
    public function getAllTasksByUserId(int $userId): Collection;
}
