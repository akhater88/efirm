<?php

namespace App\Filament\Resources\Matters\Schemas;

use App\Enums\MatterStatus;
use App\Enums\PracticeArea;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MatterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('matters.details'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('matters.title'))
                            ->required()
                            ->maxLength(255),
                        Select::make('client_id')
                            ->label(__('matters.client'))
                            ->relationship('client', 'display_name', fn ($query) => $query->where('is_client', true))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('practice_area')
                            ->label(__('matters.practice_area'))
                            ->options(collect(PracticeArea::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                            ->required()
                            ->default(PracticeArea::CommercialContracts->value),
                        Select::make('status')
                            ->label(__('matters.status'))
                            ->options(collect(MatterStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                            ->required()
                            ->default(MatterStatus::Active->value),
                        TextInput::make('stage')
                            ->label(__('matters.stage'))
                            ->maxLength(100)
                            ->helperText(__('matters.stage_helper')),
                        Textarea::make('description')
                            ->label(__('matters.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('internal_reference')
                            ->label(__('matters.internal_reference'))
                            ->maxLength(100),
                        Select::make('lead_lawyer_id')
                            ->label(__('matters.lead_lawyer'))
                            ->relationship('leadLawyer', 'name')
                            ->searchable()
                            ->preload(),
                        DatePicker::make('opened_at')
                            ->label(__('matters.opened_at'))
                            ->default(now()),
                        DatePicker::make('closed_at')
                            ->label(__('matters.closed_at')),
                        TagsInput::make('tags')
                            ->label(__('matters.tags')),
                    ])->columns(2),
            ]);
    }
}
