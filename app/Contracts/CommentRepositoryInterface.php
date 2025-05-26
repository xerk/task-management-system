<?php

namespace App\Contracts;

use App\Models\Task;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Collection;

interface CommentRepositoryInterface
{
    /**
     * Find a comment by ID.
     */
    public function find(int $id): ?Comment;

    /**
     * Create a new comment.
     */
    public function create(array $data): Comment;

    /**
     * Update an existing comment.
     */
    public function update(Comment $comment, array $data): Comment;

    /**
     * Delete a comment.
     */
    public function delete(Comment $comment): bool;

    /**
     * Get comments for a task.
     */
    public function getForTask(Task $task): Collection;

    /**
     * Get comments by a user.
     */
    public function getByUser(User $user, int $limit = 50): Collection;
}
