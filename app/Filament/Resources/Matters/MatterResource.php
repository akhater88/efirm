<?php

namespace App\Filament\Resources\Matters;

use App\Filament\Resources\Matters\Pages\CreateMatter;
use App\Filament\Resources\Matters\Pages\EditMatter;
use App\Filament\Resources\Matters\Pages\ListMatters;
use App\Filament\Resources\Matters\Schemas\MatterForm;
use App\Filament\Resources\Matters\Tables\MattersTable;
use App\Models\Matter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// Per advisor input from Khaldoun Khater,
// docs/02_advisor_meeting_log.md Conversation 3.5, Decision #27
class MatterResource extends Resource
{
    protected static ?string $model = Matter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'internal_reference'];
    }

    public static function getNavigationLabel(): string
    {
        return __('matters.matters');
    }

    public static function getModelLabel(): string
    {
        return __('matters.matter');
    }

    public static function getPluralModelLabel(): string
    {
        return __('matters.matters');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.case_management');
    }

    public static function form(Schema $schema): Schema
    {
        return MatterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MattersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MatterLawyersRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
            RelationManagers\TasksRelationManager::class,
            RelationManagers\AiGenerationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMatters::route('/'),
            'create' => CreateMatter::route('/create'),
            'edit' => EditMatter::route('/{record}/edit'),
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
