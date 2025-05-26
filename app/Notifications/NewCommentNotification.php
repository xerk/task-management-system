<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly Comment $comment
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Comment on Your Task')
            ->greeting('Hello ' . $notifiable->name)
            ->line('A new comment has been added to your task: ' . $this->comment->task->title)
            ->line('Comment by: ' . $this->comment->user->name)
            ->line('Comment: ' . $this->comment->content)
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'comment_id' => $this->comment->id,
            'task_id' => $this->comment->task_id,
            'user_id' => $this->comment->user_id,
            'content' => $this->comment->content,
        ];
    }
}
