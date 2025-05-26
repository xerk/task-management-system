<?php

use App\Models\User;
use App\Models\Task;
use App\Models\Comment;
use App\Events\CommentCreated;
use App\Listeners\SendNewCommentNotification;
use App\Notifications\NewCommentNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->taskCreator = User::factory()->create(['email' => 'creator@example.com']);
    $this->commenter = User::factory()->create(['email' => 'commenter@example.com']);

    $this->task = Task::factory()->create([
        'created_by' => $this->taskCreator->id,
        'title' => 'Test Task'
    ]);

    $this->comment = Comment::factory()->create([
        'task_id' => $this->task->id,
        'user_id' => $this->commenter->id,
        'content' => 'Test comment'
    ]);
});

test('comment created event is dispatched', function () {
    Event::fake();

    event(new CommentCreated($this->comment));

    Event::assertDispatched(CommentCreated::class);
});

test('notification is sent to task creator when comment is created', function () {
    Notification::fake();

    $listener = new SendNewCommentNotification();
    $event = new CommentCreated($this->comment);

    $listener->handle($event);

    Notification::assertSentTo(
        $this->taskCreator,
        NewCommentNotification::class
    );
});

test('notification is not sent when comment creator is task creator', function () {
    Notification::fake();

    // Create comment by task creator
    $selfComment = Comment::factory()->create([
        'task_id' => $this->task->id,
        'user_id' => $this->taskCreator->id,
        'content' => 'Self comment'
    ]);

    $listener = new SendNewCommentNotification();
    $event = new CommentCreated($selfComment);

    $listener->handle($event);

    Notification::assertNotSentTo(
        $this->taskCreator,
        NewCommentNotification::class
    );
});
