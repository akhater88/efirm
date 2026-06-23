<?php

// Per advisor input from Khaldoun Khater,
// docs/02_advisor_meeting_log.md Conversation 3.5, Decision #27

namespace App\Filament\Resources\ServiceLogEntries;

use App\Enums\ServiceMethod;
use App\Enums\ServiceStatus;
use App\Filament\Resources\ServiceLogEntries\Pages\CreateServiceLogEntry;
use App\Filament\Resources\ServiceLogEntries\Pages\EditServiceLogEntry;
use App\Filament\Resources\ServiceLogEntries\Pages\ListServiceLogEntries;
use App\Models\ServiceLogEntry;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceLogEntryResource extends Resource
{
    protected static ?string $model = ServiceLogEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('navigation.case_management.service_log');
    }

    public static function getModelLabel(): string
    {
        return __('litigation.service_log');
    }

    public static function getPluralModelLabel(): string
    {
        return __('litigation.service_log_entries');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.case_management');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('litigation.service_log'))
                    ->schema([
                        DatePicker::make('service_date')
                            ->label(__('litigation.service_date'))
                            ->required(),
                        Select::make('matter_id')
                            ->label(__('matters.matter'))
                            ->relationship('matter', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('service_method')
                            ->label(__('litigation.service_method'))
                            ->options(collect(ServiceMethod::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                            ->required(),
                        Select::make('status')
                            ->label(__('litigation.service_status'))
                            ->options(collect(ServiceStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                            ->required()
                            ->default(ServiceStatus::PendingProof->value),
                        Select::make('served_party_contact_id')
                            ->label(__('litigation.served_party'))
                            ->relationship('servedParty', 'display_name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('service_address')
                            ->label(__('litigation.service_address'))
                            ->maxLength(500),
                        TextInput::make('served_by_name')
                            ->label(__('litigation.served_by_name'))
                            ->maxLength(255),
                        TextInput::make('served_to_recipient_name')
                            ->label(__('litigation.served_to_recipient_name'))
                            ->maxLength(255),
                        Textarea::make('notes')
                            ->label(__('litigation.notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('service_date')
                    ->label(__('litigation.service_date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('matter.title')
                    ->label(__('matters.matter'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('service_method')
                    ->label(__('litigation.service_method'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ServiceMethod ? $state->label() : $state),
                TextColumn::make('status')
                    ->label(__('litigation.service_status'))
                    ->badge()
                    ->color(fn ($state) => $state instanceof ServiceStatus ? $state->color() : 'gray')
                    ->formatStateUsing(fn ($state) => $state instanceof ServiceStatus ? $state->label() : $state),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceLogEntries::route('/'),
            'create' => CreateServiceLogEntry::route('/create'),
            'edit' => EditServiceLogEntry::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
