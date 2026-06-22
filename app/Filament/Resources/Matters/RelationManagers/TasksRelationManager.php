<?php

namespace App\Filament\Resources\Matters\RelationManagers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('tasks.tasks');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('tasks.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('status')
                    ->label(__('tasks.status'))
                    ->badge()
                    ->color(fn ($state) => $state instanceof TaskStatus ? $state->color() : 'gray')
                    ->formatStateUsing(fn ($state) => $state instanceof TaskStatus ? $state->label() : $state),
                TextColumn::make('priority')
                    ->label(__('tasks.priority'))
                    ->badge()
                    ->color(fn ($state) => $state instanceof TaskPriority ? $state->color() : 'gray')
                    ->formatStateUsing(fn ($state) => $state instanceof TaskPriority ? $state->label() : $state),
                TextColumn::make('assignedTo.name')
                    ->label(__('tasks.assigned_to')),
                TextColumn::make('due_date')
                    ->label(__('tasks.due_date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('currentStage.name_'.app()->getLocale())
                    ->label(__('task_workflows.stage'))
                    ->badge()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('tasks.status'))
                    ->options(collect(TaskStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])),
                SelectFilter::make('priority')
                    ->label(__('tasks.priority'))
                    ->options(collect(TaskPriority::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])),
            ])
            ->headerActions([
                CreateAction::make()
                    ->form([
                        TextInput::make('title')
                            ->label(__('tasks.title'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label(__('tasks.description'))
                            ->rows(2),
                        Select::make('assigned_to_user_id')
                            ->label(__('tasks.assigned_to'))
                            ->relationship('assignedTo', 'name')
                            ->searchable()
                            ->preload(),
                        DatePicker::make('due_date')
                            ->label(__('tasks.due_date')),
                        Select::make('priority')
                            ->label(__('tasks.priority'))
                            ->options(collect(TaskPriority::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                            ->default('normal'),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['taskable_type'] = 'matter';
                        $data['taskable_id'] = $this->getOwnerRecord()->id;
                        $data['created_by_user_id'] = auth()->id();
                        $data['updated_by_user_id'] = auth()->id();

                        return $data;
                    }),
                Action::make('open_board')
                    ->label(__('task_workflows.task_board'))
                    ->icon('heroicon-o-view-columns')
                    ->url(fn () => route('filament.admin.pages.tasks-board', [
                        'tenant' => Filament::getTenant(),
                    ])),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_date', 'asc');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }
}
