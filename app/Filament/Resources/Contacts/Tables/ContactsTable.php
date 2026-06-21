<?php

namespace App\Filament\Resources\Contacts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label(__('contacts.type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __("contacts.{$state}"))
                    ->sortable(),
                TextColumn::make('display_name')
                    ->label(__('contacts.display_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('contacts.email'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('contacts.phone')),
                IconColumn::make('is_client')
                    ->label(__('contacts.client'))
                    ->boolean(),
                IconColumn::make('is_counterparty')
                    ->label(__('contacts.counterparty'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('contacts.type'))
                    ->options([
                        'person' => __('contacts.person'),
                        'organization' => __('contacts.organization'),
                    ]),
                TernaryFilter::make('is_client')
                    ->label(__('contacts.client')),
                TernaryFilter::make('is_counterparty')
                    ->label(__('contacts.counterparty')),
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
