<?php

namespace App\Repositories;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use App\Contracts\CommentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentRepository implements CommentRepositoryInterface
{
    /**
     * Find a comment by ID.
     *
     * @param int $id
     * @return Comment|null
     */
    public function find(int $id): ?Comment
    {
        return Comment::with(['user', 'task'])->find($id);
    }

    /**
     * Create a new comment.
     *
     * @param array $data
     * @return Comment
     */
    public function create(array $data): Comment
    {
        $comment = Comment::create($data);
        return $comment->load(['user', 'task']);
    }

    /**
     * Update an existing comment.
     *
     * @param Comment $comment
     * @param array $data
     * @return Comment
     */
    public function update(Comment $comment, array $data): Comment
    {
        $comment->update($data);
        return $comment->fresh(['user', 'task']);
    }

    /**
     * Delete a comment.
     *
     * @param Comment $comment
     * @return bool
     */
    public function delete(Comment $comment): bool
    {
        return $comment->delete();
    }

    /**
     * Get comments for a task.
     *
     * @param Task $task
     * @return Collection
     */
    public function getForTask(Task $task): Collection
    {
        return Comment::with(['user'])
            ->forTask($task->id)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get comments by a user.
     *
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getByUser(User $user, int $limit = 50): Collection
    {
        return Comment::with(['task'])
            ->byUser($user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get paginated comments for a specific task.
     *
     * @param Task $task
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByTaskPaginated(Task $task, int $perPage = 15): LengthAwarePaginator
    {
        return Comment::with(['user'])
            ->forTask($task->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Count comments for a task.
     *
     * @param Task $task
     * @return int
     */
    public function countForTask(Task $task): int
    {
        return Comment::forTask($task->id)->count();
    }

    /**
     * Get recent comments for a task.
     *
     * @param Task $task
     * @param int $limit
     * @return Collection
     */
    public function getRecentForTask(Task $task, int $limit = 5): Collection
    {
        return Comment::with(['user'])
            ->forTask($task->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
