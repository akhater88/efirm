<?php

namespace App\Filament\Resources\Matters\RelationManagers;

use App\Enums\MatterLawyerRole;
use App\Models\MatterLawyer;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MatterLawyersRelationManager extends RelationManager
{
    protected static string $relationship = 'matterLawyers';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lawyers.team');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => MatterLawyer::where('matter_id', $this->getOwnerRecord()->id)->active())
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('lawyers.lawyer'))
                    ->sortable(),
                TextColumn::make('role')
                    ->label(__('lawyers.role'))
                    ->badge()
                    ->color(fn ($state) => $state instanceof MatterLawyerRole ? $state->color() : 'gray')
                    ->formatStateUsing(fn ($state) => $state instanceof MatterLawyerRole ? $state->label() : $state),
                TextColumn::make('assigned_at')
                    ->label(__('lawyers.assigned_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('assignedBy.name')
                    ->label(__('lawyers.assigned_by'))
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('show_history')
                    ->label(__('lawyers.show_history'))
                    ->query(fn (Builder $query) => $query->withoutGlobalScope('active_only'))
                    ->toggle(),
            ])
            ->defaultSort('assigned_at', 'desc');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }
}
