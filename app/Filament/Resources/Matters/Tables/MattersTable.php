<?php

namespace App\Filament\Resources\Matters\Tables;

use App\Enums\MatterStatus;
use App\Enums\PracticeArea;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MattersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('matters.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.display_name')
                    ->label(__('matters.client'))
                    ->searchable(),
                TextColumn::make('practice_area')
                    ->label(__('matters.practice_area'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof PracticeArea ? $state->label() : $state),
                TextColumn::make('status')
                    ->label(__('matters.status'))
                    ->badge()
                    ->color(fn ($state) => $state instanceof MatterStatus ? $state->color() : 'gray')
                    ->formatStateUsing(fn ($state) => $state instanceof MatterStatus ? $state->label() : $state),
                TextColumn::make('leadLawyer.name')
                    ->label(__('matters.lead_lawyer'))
                    ->toggleable(),
                TextColumn::make('opened_at')
                    ->label(__('matters.opened_at'))
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('matters.status'))
                    ->options(collect(MatterStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
                SelectFilter::make('practice_area')
                    ->label(__('matters.practice_area'))
                    ->options(collect(PracticeArea::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
