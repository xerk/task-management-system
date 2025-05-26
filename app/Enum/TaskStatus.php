<?php

namespace App\Enum;

enum TaskStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in-progress';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
        };
    }
}
