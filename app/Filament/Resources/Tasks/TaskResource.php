<?php

namespace App\Filament\Resources\Tasks;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Filament\Resources\Tasks\Pages\CreateTask;
use App\Filament\Resources\Tasks\Pages\EditTask;
use App\Filament\Resources\Tasks\Pages\ListTasks;
use App\Models\Task;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 35;

    public static function getNavigationLabel(): string
    {
        return __('tasks.tasks');
    }

    public static function getModelLabel(): string
    {
        return __('tasks.task');
    }

    public static function getPluralModelLabel(): string
    {
        return __('tasks.tasks');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.practice');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('tasks.details'))
                ->schema([
                    TextInput::make('title')
                        ->label(__('tasks.title'))
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label(__('tasks.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                    Select::make('taskable_type')
                        ->label(__('tasks.related_to_type'))
                        ->options([
                            'matter' => __('matters.matter'),
                            'contact' => __('contacts.contact'),
                            'document' => __('documents.document'),
                            'obligation' => __('obligations.obligation'),
                        ])
                        ->required()
                        ->reactive(),
                    TextInput::make('taskable_id')
                        ->label(__('tasks.related_to_id'))
                        ->required()
                        ->maxLength(26),
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
                    Select::make('status')
                        ->label(__('tasks.status'))
                        ->options(collect(TaskStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                        ->default('todo'),
                    TagsInput::make('tags')
                        ->label(__('tasks.tags')),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('tasks.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
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
                TextColumn::make('taskable_type')
                    ->label(__('tasks.related_to_type'))
                    ->badge(),
                TextColumn::make('updated_at')
                    ->label(__('common.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('tasks.status'))
                    ->options(collect(TaskStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])),
                SelectFilter::make('priority')
                    ->label(__('tasks.priority'))
                    ->options(collect(TaskPriority::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])),
            ])
            ->defaultSort('due_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTasks::route('/'),
            'create' => CreateTask::route('/create'),
            'edit' => EditTask::route('/{record}/edit'),
        ];
    }
}
