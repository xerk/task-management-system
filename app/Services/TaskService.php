<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Enum\TaskStatus;
use App\Contracts\TaskServiceInterface;
use App\Contracts\TaskRepositoryInterface;
use App\Contracts\CacheServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TaskService implements TaskServiceInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly CacheServiceInterface $cacheService
    ) {}

    /**
     * Create a new task.
     */
    public function createTask(array $data): Task
    {
        $user = Auth::user();

        $taskData = [
            ...$data,
            'created_by' => $user->id,
            'status' => $data['status'] ?? TaskStatus::PENDING,
        ];

        $task = $this->taskRepository->create($taskData);

        // Load relationships for the response
        $task->load(['creator', 'assignee']);

        // Invalidate related caches
        $this->cacheService->invalidateTaskCaches(
            $task->id,
            $user->id,
            $task->status->value
        );

        return $task;
    }

    /**
     * Update an existing task.
     */
    public function updateTask(Task $task, array $data): Task
    {
        $user = Auth::user();

        // Check if user can update this task
        if (!$this->canUserModifyTask($task, $user)) {
            throw ValidationException::withMessages([
                'task' => ['You do not have permission to update this task.']
            ]);
        }

        $oldStatus = $task->status->value;
        $oldAssignedTo = $task->assigned_to;

        $updatedTask = $this->taskRepository->update($task, $data);

        // Invalidate related caches
        $this->cacheService->invalidateTaskCaches(
            $task->id,
            $user->id,
            $oldStatus
        );

        // If status or assignment changed, invalidate additional caches
        if (isset($data['status']) && $data['status'] !== $oldStatus) {
            $this->cacheService->invalidateTaskCaches($task->id, null, $data['status']);
        }

        if (isset($data['assigned_to']) && $data['assigned_to'] !== $oldAssignedTo) {
            $this->cacheService->invalidateTaskCaches($task->id, $data['assigned_to']);
        }

        return $updatedTask;
    }

    /**
     * Delete a task.
     */
    public function deleteTask(Task $task): bool
    {
        $user = Auth::user();

        // Check if user can delete this task
        if (!$this->canUserModifyTask($task, $user)) {
            throw ValidationException::withMessages([
                'task' => ['You do not have permission to delete this task.']
            ]);
        }

        $taskId = $task->id;
        $userId = $user->id;
        $status = $task->status->value;
        $assignedTo = $task->assigned_to;

        $result = $this->taskRepository->delete($task);

        if ($result) {
            // Invalidate related caches
            $this->cacheService->invalidateTaskCaches($taskId, $userId, $status);
            if ($assignedTo && $assignedTo !== $userId) {
                $this->cacheService->invalidateTaskCaches($taskId, $assignedTo);
            }
        }

        return $result;
    }

    /**
     * Get a task by ID.
     */
    public function getTaskById(int $id): Task
    {
        $task = $this->cacheService->cacheTask($id, function () use ($id) {
            return $this->taskRepository->find($id);
        });

        if (!$task) {
            throw ValidationException::withMessages([
                'task' => ['Task not found.']
            ]);
        }

        $user = Auth::user();

        // Check if user can view this task
        if (!$this->canUserViewTask($task, $user)) {
            throw ValidationException::withMessages([
                'task' => ['You do not have permission to view this task.']
            ]);
        }

        return $task;
    }

    /**
     * Get all tasks.
     */
    public function getAllTasks(): Collection
    {
        $user = Auth::user();

        return $this->cacheService->cacheTaskList(
            'user_tasks_all',
            function () use ($user) {
                return Task::with(['creator', 'assignee', 'comments'])
                    ->where(function ($query) use ($user) {
                        $query->where('created_by', $user->id)
                            ->orWhere('assigned_to', $user->id);
                    })
                    ->orderBy('created_at', 'desc')
                    ->get();
            },
            ['user_id' => $user->id]
        );
    }

    /**
     * Get all tasks by user ID.
     */
    public function getAllTasksByUserId(int $userId): Collection
    {
        $currentUser = Auth::user();

        // Users can only see their own tasks unless they have admin privileges
        if ($currentUser->id !== $userId) {
            throw ValidationException::withMessages([
                'user' => ['You can only view your own tasks.']
            ]);
        }

        return $this->cacheService->cacheTaskList(
            'user_tasks_specific',
            function () use ($userId) {
                return Task::with(['creator', 'assignee', 'comments'])
                    ->where(function ($query) use ($userId) {
                        $query->where('created_by', $userId)
                            ->orWhere('assigned_to', $userId);
                    })
                    ->orderBy('created_at', 'desc')
                    ->get();
            },
            ['user_id' => $userId]
        );
    }

    // Additional helper methods for extended functionality

    /**
     * Check if user can modify a task
     */
    private function canUserModifyTask(Task $task, User $user): bool
    {
        // User can modify if they created the task or if they are assigned to it
        return $task->created_by === $user->id || $task->assigned_to === $user->id;
    }

    /**
     * Check if user can view a task
     */
    private function canUserViewTask(Task $task, User $user): bool
    {
        // For now, users can view tasks they created or are assigned to
        // This can be extended for team-based permissions
        return $task->created_by === $user->id || $task->assigned_to === $user->id;
    }

    /**
     * Assign task to a user (extended functionality)
     */
    public function assignTask(Task $task, User $assignee): Task
    {
        $user = Auth::user();

        // Check if user can assign this task
        if (!$this->canUserModifyTask($task, $user)) {
            throw ValidationException::withMessages([
                'task' => ['You do not have permission to assign this task.']
            ]);
        }

        $oldAssignedTo = $task->assigned_to;
        $updatedTask = $this->taskRepository->assignToUser($task, $assignee);

        // Invalidate related caches
        $this->cacheService->invalidateTaskCaches($task->id, $user->id);
        $this->cacheService->invalidateTaskCaches($task->id, $assignee->id);

        if ($oldAssignedTo && $oldAssignedTo !== $assignee->id) {
            $this->cacheService->invalidateTaskCaches($task->id, $oldAssignedTo);
        }

        return $updatedTask;
    }

    /**
     * Update task status (extended functionality)
     */
    public function updateTaskStatus(Task $task, TaskStatus $status): Task
    {
        $user = Auth::user();

        // Check if user can update this task status
        if (!$this->canUserModifyTask($task, $user)) {
            throw ValidationException::withMessages([
                'task' => ['You do not have permission to update this task status.']
            ]);
        }

        $oldStatus = $task->status->value;
        $updatedTask = $this->taskRepository->updateStatus($task, $status);

        // Invalidate related caches
        $this->cacheService->invalidateTaskCaches($task->id, $user->id, $oldStatus);
        $this->cacheService->invalidateTaskCaches($task->id, null, $status->value);

        return $updatedTask;
    }
}
