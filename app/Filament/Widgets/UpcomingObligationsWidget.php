<?php

namespace App\Filament\Widgets;

use App\Enums\ObligationStatus;
use App\Models\Obligation;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingObligationsWidget extends TableWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return __('obligations.upcoming_obligations');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Obligation::query()
                    ->upcoming(14)
                    ->with(['document', 'responsibleUser'])
                    ->orderBy('due_date')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('title')
                    ->label(__('obligations.title'))
                    ->limit(40),
                TextColumn::make('document.title')
                    ->label(__('obligations.document'))
                    ->limit(30),
                TextColumn::make('due_date')
                    ->label(__('obligations.due_date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('obligations.status'))
                    ->badge()
                    ->color(fn ($state) => $state instanceof ObligationStatus ? $state->color() : 'gray')
                    ->formatStateUsing(fn ($state) => $state instanceof ObligationStatus ? $state->label() : $state),
                TextColumn::make('responsibleUser.name')
                    ->label(__('obligations.responsible_user')),
            ])
            ->paginated(false);
    }
}
