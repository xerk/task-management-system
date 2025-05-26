<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Comment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'content',
        'task_id',
        'user_id',
    ];

    /**
     * Get the task that owns the comment.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user that owns the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include comments for a specific task.
     */
    public function scopeForTask($query, $taskId): Builder
    {
        return $query->where('task_id', $taskId);
    }

    /**
     * Scope a query to only include comments by a specific user.
     */
    public function scopeByUser($query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
