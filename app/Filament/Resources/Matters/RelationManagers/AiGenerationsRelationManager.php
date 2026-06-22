<?php

namespace App\Filament\Resources\Matters\RelationManagers;

use App\Enums\AiDocGenerationStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AiGenerationsRelationManager extends RelationManager
{
    protected static string $relationship = 'aiDocumentGenerations';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('ai.ai_generations');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('template_key')
                    ->label(__('ai.template'))
                    ->badge(),
                TextColumn::make('user.name')
                    ->label(__('ai.requested_by')),
                TextColumn::make('status')
                    ->label(__('ai.status'))
                    ->badge()
                    ->color(fn ($state) => $state instanceof AiDocGenerationStatus ? $state->color() : 'gray')
                    ->formatStateUsing(fn ($state) => $state instanceof AiDocGenerationStatus ? $state->label() : $state),
                TextColumn::make('input_tokens')
                    ->label(__('ai.tokens'))
                    ->formatStateUsing(fn ($state, $record) => number_format($state + $record->output_tokens)),
                TextColumn::make('cost_usd')
                    ->label(__('ai.cost'))
                    ->money('usd'),
                TextColumn::make('created_at')
                    ->label(__('common.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }
}
