<?php

namespace App\Repositories;

use App\Models\Task;
use App\Models\User;
use App\Enum\TaskStatus;
use App\Contracts\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskRepository implements TaskRepositoryInterface
{
    /**
     * Find a task by ID.
     *
     * @param int $id
     * @return Task|null
     */
    public function find(int $id): ?Task
    {
        return Task::with(['creator', 'assignee', 'comments.user'])->find($id);
    }

    /**
     * Create a new task.
     *
     * @param array $data
     * @return Task
     */
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    /**
     * Update an existing task.
     *
     * @param Task $task
     * @param array $data
     * @return Task
     */
    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task->fresh(['creator', 'assignee', 'comments.user']);
    }

    /**
     * Delete a task.
     *
     * @param Task $task
     * @return bool
     */
    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    /**
     * Get paginated tasks with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Task::with(['creator', 'assignee', 'comments']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->status($filters['status']);
        }

        if (isset($filters['assigned_to'])) {
            $query->assignedTo($filters['assigned_to']);
        }

        if (isset($filters['created_by'])) {
            $query->createdBy($filters['created_by']);
        }

        if (isset($filters['due_date_from'])) {
            $query->where('due_date', '>=', $filters['due_date_from']);
        }

        if (isset($filters['due_date_to'])) {
            $query->where('due_date', '<=', $filters['due_date_to']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get tasks assigned to a user.
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAssignedToUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Task::with(['creator', 'assignee', 'comments'])
            ->assignedTo($user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get tasks created by a user.
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getCreatedByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Task::with(['creator', 'assignee', 'comments'])
            ->createdBy($user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get tasks by status
     *
     * @param TaskStatus $status
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByStatus(TaskStatus $status, int $perPage = 15): LengthAwarePaginator
    {
        return Task::with(['creator', 'assignee', 'comments'])
            ->status($status)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Assign task to user
     *
     * @param Task $task
     * @param User $user
     * @return Task
     */
    public function assignToUser(Task $task, User $user): Task
    {
        $task->update(['assigned_to' => $user->id]);
        return $task->fresh(['creator', 'assignee', 'comments.user']);
    }

    /**
     * Update task status
     *
     * @param Task $task
     * @param TaskStatus $status
     * @return Task
     */
    public function updateStatus(Task $task, TaskStatus $status): Task
    {
        $task->update(['status' => $status]);
        return $task->fresh(['creator', 'assignee', 'comments.user']);
    }
}
