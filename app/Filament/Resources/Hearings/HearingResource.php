<?php

// Per advisor input from Khaldoun Khater,
// docs/02_advisor_meeting_log.md Conversation 3.5, Decision #27

namespace App\Filament\Resources\Hearings;

use App\Enums\HearingStatus;
use App\Enums\HearingType;
use App\Filament\Resources\Hearings\Pages\CreateHearing;
use App\Filament\Resources\Hearings\Pages\EditHearing;
use App\Filament\Resources\Hearings\Pages\ListHearings;
use App\Models\Hearing;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
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

class HearingResource extends Resource
{
    protected static ?string $model = Hearing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('navigation.case_management.hearings');
    }

    public static function getModelLabel(): string
    {
        return __('litigation.hearing');
    }

    public static function getPluralModelLabel(): string
    {
        return __('litigation.hearings');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.case_management');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('litigation.hearing'))
                    ->schema([
                        DateTimePicker::make('hearing_date')
                            ->label(__('litigation.hearing_date'))
                            ->required(),
                        Select::make('matter_id')
                            ->label(__('matters.matter'))
                            ->relationship('matter', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('hearing_type')
                            ->label(__('litigation.hearing_type'))
                            ->options(collect(HearingType::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                            ->required(),
                        Select::make('status')
                            ->label(__('litigation.hearing_status'))
                            ->options(collect(HearingStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                            ->required()
                            ->default(HearingStatus::Scheduled->value),
                        Select::make('court_id')
                            ->label(__('litigation.court'))
                            ->relationship('court', app()->getLocale() === 'ar' ? 'name_ar' : 'name_en')
                            ->searchable()
                            ->preload(),
                        Select::make('assigned_lawyer_user_id')
                            ->label(__('matters.lead_lawyer'))
                            ->relationship('assignedLawyer', 'name')
                            ->searchable()
                            ->preload(),
                        Textarea::make('outcome')
                            ->label(__('litigation.outcome'))
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('next_action_required')
                            ->label(__('litigation.next_action_required'))
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('hearing_date')
                    ->label(__('litigation.hearing_date'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('matter.title')
                    ->label(__('matters.matter'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('hearing_type')
                    ->label(__('litigation.hearing_type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof HearingType ? $state->label() : $state),
                TextColumn::make('status')
                    ->label(__('litigation.hearing_status'))
                    ->badge()
                    ->color(fn ($state) => $state instanceof HearingStatus ? $state->color() : 'gray')
                    ->formatStateUsing(fn ($state) => $state instanceof HearingStatus ? $state->label() : $state),
                TextColumn::make('court.name_en')
                    ->label(__('litigation.court'))
                    ->toggleable(),
                TextColumn::make('assignedLawyer.name')
                    ->label(__('matters.lead_lawyer'))
                    ->toggleable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHearings::route('/'),
            'create' => CreateHearing::route('/create'),
            'edit' => EditHearing::route('/{record}/edit'),
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
