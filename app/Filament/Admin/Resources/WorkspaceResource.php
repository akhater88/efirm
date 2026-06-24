<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WorkspaceResource\Pages;
use App\Models\Workspace;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkspaceResource extends Resource
{
    protected static ?string $model = Workspace::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.workspaces');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.group_platform');
    }

    public static function getModelLabel(): string
    {
        return __('admin.workspaces.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.workspaces.plural');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return auth()->guard('admin')->check();
    }

    public static function canView($record): bool
    {
        return auth()->guard('admin')->check();
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Workspace::withoutGlobalScopes())
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.workspaces.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label(__('admin.workspaces.slug'))
                    ->searchable(),

                TextColumn::make('members_count')
                    ->label(__('admin.workspaces.members_count'))
                    ->counts('members')
                    ->sortable(),

                TextColumn::make('default_locale')
                    ->label(__('admin.workspaces.locale'))
                    ->badge(),

                TextColumn::make('pdpl_consent_obtained')
                    ->label(__('admin.workspaces.pdpl_consent'))
                    ->badge()
                    ->getStateUsing(fn (Workspace $record): string => $record->pdpl_consent_obtained
                        ? __('admin.workspaces.consent_yes')
                        : __('admin.workspaces.consent_no')
                    )
                    ->color(fn (Workspace $record): string => $record->pdpl_consent_obtained ? 'success' : 'warning'),

                TextColumn::make('created_at')
                    ->label(__('admin.workspaces.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (Workspace $record): string => static::getUrl('view', ['record' => $record]));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkspaces::route('/'),
            'view' => Pages\ViewWorkspace::route('/{record}'),
        ];
    }
}
