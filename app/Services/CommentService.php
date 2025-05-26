<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use App\Contracts\CommentServiceInterface;
use App\Contracts\CommentRepositoryInterface;
use App\Contracts\TaskRepositoryInterface;
use App\Contracts\CacheServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CommentService implements CommentServiceInterface
{
    public function __construct(
        private readonly CommentRepositoryInterface $commentRepository,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly CacheServiceInterface $cacheService
    ) {}

    /**
     * Create a new comment.
     */
    public function createComment(array $data): Comment
    {
        $user = Auth::user();
        $task = $this->taskRepository->find($data['task_id']);

        if (!$task) {
            throw ValidationException::withMessages([
                'task_id' => ['Task not found.']
            ]);
        }

        $commentData = [
            ...$data,
            'user_id' => $user->id,
        ];

        $comment = $this->commentRepository->create($commentData);

        // Invalidate related caches
        $this->cacheService->invalidateCommentCaches($task->id, $user->id);

        // Dispatch the CommentCreated event
        event(new \App\Events\CommentCreated($comment));

        return $comment;
    }

    /**
     * Update an existing comment.
     */
    public function updateComment(Comment $comment, array $data): Comment
    {
        $user = Auth::user();

        // Check if user can update this comment
        if (!$this->canUserModifyComment($comment, $user)) {
            throw ValidationException::withMessages([
                'comment' => ['You do not have permission to update this comment.']
            ]);
        }

        $updatedComment = $this->commentRepository->update($comment, $data);

        // Invalidate related caches
        $this->cacheService->invalidateCommentCaches($comment->task_id, $user->id);

        return $updatedComment;
    }

    /**
     * Delete a comment.
     */
    public function deleteComment(Comment $comment): bool
    {
        $user = Auth::user();

        // Check if user can delete this comment
        if (!$this->canUserModifyComment($comment, $user)) {
            throw ValidationException::withMessages([
                'comment' => ['You do not have permission to delete this comment.']
            ]);
        }

        $taskId = $comment->task_id;
        $userId = $user->id;

        $result = $this->commentRepository->delete($comment);

        if ($result) {
            // Invalidate related caches
            $this->cacheService->invalidateCommentCaches($taskId, $userId);
        }

        return $result;
    }

    /**
     * Get a comment by ID.
     */
    public function getCommentById(Comment $comment): Comment
    {
        if (!$comment) {
            throw ValidationException::withMessages([
                'comment' => ['Comment not found.']
            ]);
        }

        $user = Auth::user();

        // Check if user can view this comment
        if (!$this->canUserViewComment($comment, $user)) {
            throw ValidationException::withMessages([
                'comment' => ['You do not have permission to view this comment.']
            ]);
        }

        return $comment;
    }

    /**
     * Get comments for a task.
     */
    public function getCommentsForTask(Task $task): Collection
    {
        $user = Auth::user();

        if (!$task) {
            throw ValidationException::withMessages([
                'task_id' => ['Task not found.']
            ]);
        }

        // Allow anyone to view comments for a task
        return $this->cacheService->cacheTaskComments($task->id, function () use ($task) {
            return $this->commentRepository->getForTask($task);
        });
    }

    /**
     * Get comments by a user.
     */
    public function getCommentsByUser(User $user): Collection
    {
        $currentUser = Auth::user();

        // Users can only see their own comments unless they have admin privileges
        if ($currentUser->id !== $user->id) {
            throw ValidationException::withMessages([
                'user' => ['You can only view your own comments.']
            ]);
        }

        if (!$user) {
            throw ValidationException::withMessages([
                'user_id' => ['User not found.']
            ]);
        }

        return $this->cacheService->cacheUserComments($user->id, function () use ($user) {
            return $this->commentRepository->getByUser($user);
        });
    }

    /**
     * Check if user can modify a comment
     */
    private function canUserModifyComment(Comment $comment, User $user): bool
    {
        // Users can only modify their own comments
        return $comment->user_id === $user->id;
    }

    /**
     * Check if user can view a comment
     */
    private function canUserViewComment(Comment $comment, User $user): bool
    {
        // Users can view comments if they can view the associated task
        return Auth::check();
    }
}
