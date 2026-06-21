<?php

namespace App\Filament\Resources\TimeEntries;

use App\Filament\Resources\TimeEntries\Pages\CreateTimeEntry;
use App\Filament\Resources\TimeEntries\Pages\EditTimeEntry;
use App\Filament\Resources\TimeEntries\Pages\ListTimeEntries;
use App\Models\TimeEntry;
use BackedEnum;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TimeEntryResource extends Resource
{
    protected static ?string $model = TimeEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?int $navigationSort = 36;

    public static function getNavigationLabel(): string
    {
        return __('time_entries.time_entries');
    }

    public static function getModelLabel(): string
    {
        return __('time_entries.time_entry');
    }

    public static function getPluralModelLabel(): string
    {
        return __('time_entries.time_entries');
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Practice';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('time_entries.details'))
                ->schema([
                    Select::make('matter_id')
                        ->label(__('time_entries.matter'))
                        ->relationship('matter', 'title')
                        ->searchable()
                        ->preload(),
                    Select::make('document_id')
                        ->label(__('time_entries.document'))
                        ->relationship('document', 'title')
                        ->searchable()
                        ->preload(),
                    Select::make('task_id')
                        ->label(__('time_entries.task'))
                        ->relationship('task', 'title')
                        ->searchable()
                        ->preload(),
                    Textarea::make('description')
                        ->label(__('time_entries.description'))
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),
                    TextInput::make('duration_minutes')
                        ->label(__('time_entries.duration_minutes'))
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(1440),
                    DateTimePicker::make('started_at')
                        ->label(__('time_entries.started_at'))
                        ->required(),
                    DateTimePicker::make('ended_at')
                        ->label(__('time_entries.ended_at'))
                        ->required(),
                    Checkbox::make('is_billable')
                        ->label(__('time_entries.is_billable'))
                        ->default(true),
                    TextInput::make('billing_rate_per_hour')
                        ->label(__('time_entries.billing_rate'))
                        ->numeric(),
                    TextInput::make('currency')
                        ->label(__('time_entries.currency'))
                        ->maxLength(3)
                        ->placeholder('USD'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('time_entries.user'))
                    ->sortable(),
                TextColumn::make('matter.title')
                    ->label(__('time_entries.matter'))
                    ->limit(30),
                TextColumn::make('description')
                    ->label(__('time_entries.description'))
                    ->limit(40),
                TextColumn::make('duration_minutes')
                    ->label(__('time_entries.duration'))
                    ->formatStateUsing(fn ($state) => floor($state / 60).'h '.($state % 60).'m')
                    ->sortable(),
                IconColumn::make('is_billable')
                    ->label(__('time_entries.billable'))
                    ->boolean(),
                TextColumn::make('started_at')
                    ->label(__('time_entries.started_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('billable')
                    ->label(__('time_entries.billable_only'))
                    ->query(fn (Builder $query) => $query->where('is_billable', true)),
            ])
            ->defaultSort('started_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTimeEntries::route('/'),
            'create' => CreateTimeEntry::route('/create'),
            'edit' => EditTimeEntry::route('/{record}/edit'),
        ];
    }
}
