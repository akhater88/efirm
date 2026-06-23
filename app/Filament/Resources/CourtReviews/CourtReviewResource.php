<?php

// Per advisor input from Khaldoun Khater,
// docs/02_advisor_meeting_log.md Conversation 3.5, Decision #27

namespace App\Filament\Resources\CourtReviews;

use App\Enums\DecisionOutcome;
use App\Enums\DecisionType;
use App\Filament\Resources\CourtReviews\Pages\CreateCourtReview;
use App\Filament\Resources\CourtReviews\Pages\EditCourtReview;
use App\Filament\Resources\CourtReviews\Pages\ListCourtReviews;
use App\Models\CourtReview;
use BackedEnum;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourtReviewResource extends Resource
{
    protected static ?string $model = CourtReview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('navigation.case_management.court_reviews');
    }

    public static function getModelLabel(): string
    {
        return __('litigation.court_review');
    }

    public static function getPluralModelLabel(): string
    {
        return __('litigation.court_reviews');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.case_management');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('litigation.court_review'))
                    ->schema([
                        DatePicker::make('decision_date')
                            ->label(__('litigation.decision_date'))
                            ->required(),
                        Select::make('matter_id')
                            ->label(__('matters.matter'))
                            ->relationship('matter', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('hearing_id')
                            ->label(__('litigation.hearing'))
                            ->relationship('hearing', 'hearing_date')
                            ->searchable()
                            ->preload(),
                        Select::make('decision_type')
                            ->label(__('litigation.decision_type'))
                            ->options(collect(DecisionType::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                            ->required(),
                        Select::make('outcome')
                            ->label(__('litigation.decision_outcome'))
                            ->options(collect(DecisionOutcome::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                            ->required(),
                        Textarea::make('summary_ar')
                            ->label(__('litigation.summary_ar'))
                            ->rows(3),
                        Textarea::make('summary_en')
                            ->label(__('litigation.summary_en'))
                            ->rows(3),
                        Checkbox::make('appealable')
                            ->label(__('litigation.appealable')),
                        DatePicker::make('appeal_deadline_date')
                            ->label(__('litigation.appeal_deadline_date')),
                        Checkbox::make('appeal_filed')
                            ->label(__('litigation.appeal_filed')),
                        Textarea::make('next_steps')
                            ->label(__('litigation.next_steps'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('decision_date')
                    ->label(__('litigation.decision_date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('matter.title')
                    ->label(__('matters.matter'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('decision_type')
                    ->label(__('litigation.decision_type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof DecisionType ? $state->label() : $state),
                TextColumn::make('outcome')
                    ->label(__('litigation.decision_outcome'))
                    ->badge()
                    ->color(fn ($state) => $state instanceof DecisionOutcome ? $state->color() : 'gray')
                    ->formatStateUsing(fn ($state) => $state instanceof DecisionOutcome ? $state->label() : $state),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourtReviews::route('/'),
            'create' => CreateCourtReview::route('/create'),
            'edit' => EditCourtReview::route('/{record}/edit'),
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
