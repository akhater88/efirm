<?php

namespace App\Filament\Admin\Resources;

use App\Enums\AdminRole;
use App\Filament\Admin\Resources\PlanResource\Pages;
use App\Models\AdminUser;
use App\Models\Plan;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.plans');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.group_billing');
    }

    public static function getModelLabel(): string
    {
        return __('admin.plans.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.plans.plural');
    }

    public static function canAccess(): bool
    {
        $admin = auth()->guard('admin')->user();

        return $admin instanceof AdminUser
            && in_array($admin->role, [AdminRole::SuperAdmin, AdminRole::Finance]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('admin.plans.section_details'))
                    ->schema([
                        TextInput::make('slug')
                            ->label(__('admin.plans.slug'))
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->alphaDash(),

                        TextInput::make('name')
                            ->label(__('admin.plans.name_en'))
                            ->required()
                            ->maxLength(100),

                        TextInput::make('name_ar')
                            ->label(__('admin.plans.name_ar'))
                            ->required()
                            ->maxLength(100),

                        Textarea::make('description')
                            ->label(__('admin.plans.description_en'))
                            ->rows(2),

                        Textarea::make('description_ar')
                            ->label(__('admin.plans.description_ar'))
                            ->rows(2),
                    ])->columns(2),

                Section::make(__('admin.plans.section_pricing'))
                    ->schema([
                        TextInput::make('price_per_seat_usd')
                            ->label(__('admin.plans.price_per_seat'))
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->step(0.01),

                        TextInput::make('sort_order')
                            ->label(__('admin.plans.sort_order'))
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label(__('admin.plans.is_active'))
                            ->default(true),
                    ])->columns(3),

                Section::make(__('admin.plans.section_limits'))
                    ->schema([
                        TextInput::make('max_seats')
                            ->label(__('admin.plans.max_seats'))
                            ->numeric()
                            ->nullable()
                            ->helperText(__('admin.plans.null_unlimited')),

                        TextInput::make('max_matters')
                            ->label(__('admin.plans.max_matters'))
                            ->numeric()
                            ->nullable()
                            ->helperText(__('admin.plans.null_unlimited')),

                        TextInput::make('max_contacts')
                            ->label(__('admin.plans.max_contacts'))
                            ->numeric()
                            ->nullable()
                            ->helperText(__('admin.plans.null_unlimited')),

                        TextInput::make('max_storage_mb')
                            ->label(__('admin.plans.max_storage_mb'))
                            ->numeric()
                            ->nullable()
                            ->suffix('MB')
                            ->helperText(__('admin.plans.null_unlimited')),
                    ])->columns(4),

                Section::make(__('admin.plans.section_features'))
                    ->schema([
                        KeyValue::make('features')
                            ->label(__('admin.plans.features'))
                            ->keyLabel(__('admin.plans.feature_key'))
                            ->valueLabel(__('admin.plans.feature_value')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')
                    ->label(__('admin.plans.slug'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label(__('admin.plans.name_en'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price_per_seat_usd')
                    ->label(__('admin.plans.price_per_seat'))
                    ->money('usd')
                    ->sortable(),

                TextColumn::make('max_seats')
                    ->label(__('admin.plans.max_seats'))
                    ->default('∞')
                    ->sortable(),

                TextColumn::make('max_matters')
                    ->label(__('admin.plans.max_matters'))
                    ->default('∞'),

                IconColumn::make('is_active')
                    ->label(__('admin.plans.is_active'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label(__('admin.plans.sort_order'))
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordUrl(fn (Plan $record): string => static::getUrl('edit', ['record' => $record]));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
