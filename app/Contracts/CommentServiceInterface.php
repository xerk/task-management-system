<?php

namespace App\Contracts;

use App\Models\Task;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Support\Collection;

interface CommentServiceInterface
{
    /**
     * Create a new comment.
     */
    public function createComment(array $data): Comment;

    /**
     * Update an existing comment.
     */
    public function updateComment(Comment $comment, array $data): Comment;

    /**
     * Delete a comment.
     */
    public function deleteComment(Comment $comment): bool;

    /**
     * Get a comment by ID.
     */
    public function getCommentById(Comment $comment): Comment;

    /**
     * Get comments for a task.
     */
    public function getCommentsForTask(TasK $task): Collection;

    /**
     * Get comments by a user.
     */
    public function getCommentsByUser(User $user): Collection;
}
