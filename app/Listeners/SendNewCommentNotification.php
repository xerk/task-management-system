<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use App\Notifications\NewCommentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendNewCommentNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(CommentCreated $event): void
    {
        Log::info('Sending new comment notification');
        $comment = $event->comment;
        $task = $comment->task;
        $taskCreator = $task->creator;

        try {
            // Don't notify if the comment creator is the task creator
            if ($comment->user_id !== $taskCreator->id) {
                $taskCreator->notify(new NewCommentNotification($comment));

                Log::info('New comment notification sent', [
                    'task_id' => $task->id,
                    'comment_id' => $comment->id,
                    'creator_id' => $taskCreator->id,
                    'commenter_id' => $comment->user_id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send new comment notification', [
                'task_id' => $task->id,
                'comment_id' => $comment->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
