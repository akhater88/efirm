<?php

namespace App\Filament\Resources\Obligations;

use App\Enums\ObligationStatus;
use App\Enums\ObligationType;
use App\Enums\ResponsibleParty;
use App\Filament\Resources\Obligations\Pages\CreateObligation;
use App\Filament\Resources\Obligations\Pages\EditObligation;
use App\Filament\Resources\Obligations\Pages\ListObligations;
use App\Models\Obligation;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ObligationResource extends Resource
{
    protected static ?string $model = Obligation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 40;

    public static function getNavigationLabel(): string
    {
        return __('obligations.obligations');
    }

    public static function getModelLabel(): string
    {
        return __('obligations.obligation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('obligations.obligations');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.documents');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('obligations.details'))
                ->schema([
                    TextInput::make('title')
                        ->label(__('obligations.title'))
                        ->required()
                        ->maxLength(255),
                    Select::make('document_id')
                        ->label(__('obligations.document'))
                        ->relationship('document', 'title')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Select::make('obligation_type')
                        ->label(__('obligations.obligation_type'))
                        ->options(collect(ObligationType::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                        ->required(),
                    Select::make('responsible_party')
                        ->label(__('obligations.responsible_party'))
                        ->options(collect(ResponsibleParty::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                        ->required(),
                    Select::make('responsible_user_id')
                        ->label(__('obligations.responsible_user'))
                        ->relationship('responsibleUser', 'name')
                        ->searchable()
                        ->preload(),
                    DatePicker::make('due_date')
                        ->label(__('obligations.due_date'))
                        ->required(),
                    TextInput::make('reminder_days_before')
                        ->label(__('obligations.reminder_days'))
                        ->numeric()
                        ->default(7),
                    Select::make('status')
                        ->label(__('obligations.status'))
                        ->options(collect(ObligationStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                        ->default('pending'),
                    TextInput::make('monetary_amount')
                        ->label(__('obligations.monetary_amount'))
                        ->numeric(),
                    TextInput::make('monetary_currency')
                        ->label(__('obligations.monetary_currency'))
                        ->maxLength(3)
                        ->placeholder('USD'),
                    Textarea::make('description')
                        ->label(__('obligations.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                    Textarea::make('notes')
                        ->label(__('obligations.notes'))
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('obligations.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('document.title')
                    ->label(__('obligations.document'))
                    ->limit(30),
                TextColumn::make('obligation_type')
                    ->label(__('obligations.obligation_type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ObligationType ? $state->label() : $state),
                TextColumn::make('due_date')
                    ->label(__('obligations.due_date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('obligations.status'))
                    ->badge()
                    ->color(fn ($state) => $state instanceof ObligationStatus ? $state->color() : 'gray')
                    ->formatStateUsing(fn ($state) => $state instanceof ObligationStatus ? $state->label() : $state),
                TextColumn::make('responsible_party')
                    ->label(__('obligations.responsible_party'))
                    ->formatStateUsing(fn ($state) => $state instanceof ResponsibleParty ? $state->label() : $state),
                TextColumn::make('monetary_amount')
                    ->label(__('obligations.monetary_amount'))
                    ->money(fn ($record) => $record->monetary_currency ?? 'USD')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('obligations.status'))
                    ->options(collect(ObligationStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])),
                SelectFilter::make('obligation_type')
                    ->label(__('obligations.obligation_type'))
                    ->options(collect(ObligationType::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])),
            ])
            ->defaultSort('due_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListObligations::route('/'),
            'create' => CreateObligation::route('/create'),
            'edit' => EditObligation::route('/{record}/edit'),
        ];
    }
}
