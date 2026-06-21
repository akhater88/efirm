<?php

namespace App\Filament\Resources\Library;

use App\Enums\ClauseLanguage;
use App\Enums\PracticeArea;
use App\Enums\RiskPosition;
use App\Filament\Resources\Library\Pages\CreateLibraryClause;
use App\Filament\Resources\Library\Pages\EditLibraryClause;
use App\Filament\Resources\Library\Pages\ListLibraryClauses;
use App\Models\LibraryClause;
use BackedEnum;
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

class LibraryClauseResource extends Resource
{
    protected static ?string $model = LibraryClause::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationLabel(): string
    {
        return __('library.clause_library');
    }

    public static function getModelLabel(): string
    {
        return __('library.clause');
    }

    public static function getPluralModelLabel(): string
    {
        return __('library.clauses');
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Documents';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('library.clause_details'))
                ->schema([
                    TextInput::make('title')
                        ->label(__('library.title'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('clause_type')
                        ->label(__('library.clause_type'))
                        ->maxLength(100)
                        ->placeholder(__('library.clause_type_placeholder')),
                    Select::make('practice_area')
                        ->label(__('library.practice_area'))
                        ->options(collect(PracticeArea::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
                    Select::make('language')
                        ->label(__('library.language'))
                        ->options(collect(ClauseLanguage::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                        ->default('mixed'),
                    Select::make('risk_position')
                        ->label(__('library.risk_position'))
                        ->options(collect(RiskPosition::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
                    Select::make('is_fallback_of_id')
                        ->label(__('library.fallback_of'))
                        ->relationship('fallbackOf', 'title')
                        ->searchable()
                        ->preload(),
                    TagsInput::make('tags')
                        ->label(__('library.tags')),
                ])->columns(2),
            Section::make(__('library.clause_content'))
                ->schema([
                    Textarea::make('body_ar_text')
                        ->label(__('library.body_ar'))
                        ->rows(6)
                        ->helperText(__('library.body_helper')),
                    Textarea::make('body_en_text')
                        ->label(__('library.body_en'))
                        ->rows(6)
                        ->helperText(__('library.body_helper')),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('library.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('clause_type')
                    ->label(__('library.clause_type'))
                    ->badge(),
                TextColumn::make('practice_area')
                    ->label(__('library.practice_area'))
                    ->formatStateUsing(fn ($state) => $state instanceof PracticeArea ? $state->label() : $state),
                TextColumn::make('risk_position')
                    ->label(__('library.risk_position'))
                    ->badge()
                    ->color(fn ($state) => $state instanceof RiskPosition ? $state->color() : 'gray')
                    ->formatStateUsing(fn ($state) => $state instanceof RiskPosition ? $state->label() : $state),
                TextColumn::make('language')
                    ->label(__('library.language'))
                    ->formatStateUsing(fn ($state) => $state instanceof ClauseLanguage ? $state->label() : $state),
                TextColumn::make('usage_count')
                    ->label(__('library.usage_count'))
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('common.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('clause_type')
                    ->label(__('library.clause_type'))
                    ->options([
                        'limitation_of_liability' => __('library.type_limitation_of_liability'),
                        'governing_law' => __('library.type_governing_law'),
                        'indemnification' => __('library.type_indemnification'),
                        'termination' => __('library.type_termination'),
                        'confidentiality' => __('library.type_confidentiality'),
                        'force_majeure' => __('library.type_force_majeure'),
                        'dispute_resolution' => __('library.type_dispute_resolution'),
                    ]),
                SelectFilter::make('risk_position')
                    ->label(__('library.risk_position'))
                    ->options(collect(RiskPosition::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
                SelectFilter::make('practice_area')
                    ->label(__('library.practice_area'))
                    ->options(collect(PracticeArea::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLibraryClauses::route('/'),
            'create' => CreateLibraryClause::route('/create'),
            'edit' => EditLibraryClause::route('/{record}/edit'),
        ];
    }
}
