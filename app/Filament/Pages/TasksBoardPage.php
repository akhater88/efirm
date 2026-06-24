<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class TasksBoardPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedViewColumns;

    protected static ?int $navigationSort = 37;

    protected static ?string $slug = 'tasks-board';

    protected Width|string|null $maxContentWidth = Width::Full;

    public static function getNavigationLabel(): string
    {
        return __('task_workflows.task_board');
    }

    public function getTitle(): string
    {
        return __('task_workflows.task_board');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.practice');
    }

    public function getView(): string
    {
        return 'filament.pages.tasks-board';
    }
}
