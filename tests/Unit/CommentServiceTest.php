<?php

use App\Models\User;
use App\Models\Task;
use App\Models\Comment;
use App\Services\CommentService;
use App\Events\CommentCreated;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->task = Task::factory()->create([
        'created_by' => $this->user->id,
    ]);

    $this->commentService = app(CommentService::class);
});

test('it creates comment successfully', function () {
    Event::fake();

    $commentData = [
        'task_id' => $this->task->id,
        'content' => 'Test comment',
    ];

    $comment = $this->commentService->createComment($commentData);

    expect($comment)->toBeInstanceOf(Comment::class);
    expect($comment->content)->toBe('Test comment');
    expect($comment->user_id)->toBe($this->user->id);
    expect($comment->task_id)->toBe($this->task->id);

    Event::assertDispatched(CommentCreated::class);
});

test('it updates comment successfully', function () {
    $comment = Comment::factory()->create([
        'user_id' => $this->user->id,
        'task_id' => $this->task->id,
    ]);

    $updatedComment = $this->commentService->updateComment($comment, [
        'content' => 'Updated content',
    ]);

    expect($updatedComment->content)->toBe('Updated content');
});

test('it deletes comment successfully', function () {
    $comment = Comment::factory()->create([
        'user_id' => $this->user->id,
        'task_id' => $this->task->id,
    ]);

    $result = $this->commentService->deleteComment($comment);

    expect($result)->toBeTrue();
    expect(Comment::find($comment->id))->toBeNull();
});

test('it gets comments for task', function () {
    Comment::factory()->count(3)->create([
        'task_id' => $this->task->id,
    ]);

    $comments = $this->commentService->getCommentsForTask($this->task);

    expect($comments)->toHaveCount(3);
});
