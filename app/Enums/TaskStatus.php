<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Blocked = 'blocked';
    case Done = 'done';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Todo => __('tasks.status_todo'),
            self::InProgress => __('tasks.status_in_progress'),
            self::Blocked => __('tasks.status_blocked'),
            self::Done => __('tasks.status_done'),
            self::Cancelled => __('tasks.status_cancelled'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Todo => 'gray',
            self::InProgress => 'info',
            self::Blocked => 'danger',
            self::Done => 'success',
            self::Cancelled => 'warning',
        };
    }
}
