<?php

namespace App\Http\Controllers\API;

use App\Models\Task;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Contracts\CommentServiceInterface;
use App\Http\Requests\Comment\CreateCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;

class CommentController extends Controller
{
    public function __construct(
        private readonly CommentServiceInterface $commentService
    ) {}

    /**
     * Display comments for a specific task.
     */
    public function index(Task $task): JsonResponse
    {
        $comments = $this->commentService->getCommentsForTask($task);

        return response()->json([
            'success' => true,
            'message' => 'Comments retrieved successfully.',
            'data' => CommentResource::collection($comments)
        ]);
    }

    /**
     * Store a newly created comment.
     */
    public function store(CreateCommentRequest $request): JsonResponse
    {
        $comment = $this->commentService->createComment($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Comment created successfully.',
            'data' => new CommentResource($comment)
        ], 201);
    }

    /**
     * Display the specified comment.
     */
    public function show(Comment $comment): JsonResponse
    {
        $comment = $this->commentService->getCommentById($comment);

        return response()->json([
            'success' => true,
            'message' => 'Comment retrieved successfully.',
            'data' => new CommentResource($comment)
        ]);
    }

    /**
     * Update the specified comment.
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        $updatedComment = $this->commentService->updateComment($comment, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully.',
            'data' => new CommentResource($updatedComment)
        ]);
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        $this->commentService->deleteComment($comment);

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully.',
            'data' => null
        ]);
    }

    /**
     * Get comments by a specific user.
     */
    public function userComments(User $user): JsonResponse
    {
        $comments = $this->commentService->getCommentsByUser($user);

        return response()->json([
            'success' => true,
            'message' => 'User comments retrieved successfully.',
            'data' => CommentResource::collection($comments)
        ]);
    }
}
