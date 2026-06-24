<?php

namespace App\Filament\Admin\Resources;

use App\Enums\AdminActivityEventType;
use App\Filament\Admin\Resources\AdminActivityLogResource\Pages;
use App\Models\AdminActivityLog;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdminActivityLogResource extends Resource
{
    protected static ?string $model = AdminActivityLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.activity_log');
    }

    public static function getModelLabel(): string
    {
        return __('admin.activity_log.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.activity_log.plural');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('occurred_at')
                    ->label(__('admin.activity_log.occurred_at'))
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('adminUser.name')
                    ->label(__('admin.activity_log.admin_user'))
                    ->placeholder(__('admin.activity_log.system'))
                    ->searchable(),

                TextColumn::make('event_type')
                    ->label(__('admin.activity_log.event_type'))
                    ->badge()
                    ->formatStateUsing(fn (AdminActivityEventType $state): string => $state->label()),

                TextColumn::make('ip_address')
                    ->label(__('admin.activity_log.ip_address'))
                    ->toggleable(),

                TextColumn::make('attempted_email')
                    ->label(__('admin.activity_log.attempted_email'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('payload')
                    ->label(__('admin.activity_log.payload'))
                    ->limit(50)
                    ->formatStateUsing(fn (mixed $state): string => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE) : (string) $state)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event_type')
                    ->label(__('admin.activity_log.event_type'))
                    ->options(collect(AdminActivityEventType::cases())->mapWithKeys(
                        fn (AdminActivityEventType $type) => [$type->value => $type->label()]
                    )),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->actions([
                ViewAction::make(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('occurred_at')
                    ->label(__('admin.activity_log.occurred_at'))
                    ->dateTime(),

                TextEntry::make('adminUser.name')
                    ->label(__('admin.activity_log.admin_user'))
                    ->placeholder(__('admin.activity_log.system')),

                TextEntry::make('event_type')
                    ->label(__('admin.activity_log.event_type'))
                    ->badge()
                    ->formatStateUsing(fn (AdminActivityEventType $state): string => $state->label()),

                TextEntry::make('attempted_email')
                    ->label(__('admin.activity_log.attempted_email'))
                    ->placeholder('-'),

                TextEntry::make('target_type')
                    ->label(__('admin.activity_log.target_type'))
                    ->placeholder('-'),

                TextEntry::make('target_id')
                    ->label(__('admin.activity_log.target_id'))
                    ->placeholder('-'),

                TextEntry::make('ip_address')
                    ->label(__('admin.activity_log.ip_address')),

                TextEntry::make('user_agent')
                    ->label(__('admin.activity_log.user_agent'))
                    ->placeholder('-'),

                TextEntry::make('payload')
                    ->label(__('admin.activity_log.payload'))
                    ->formatStateUsing(fn (mixed $state): string => is_array($state)
                        ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        : (string) $state
                    )
                    ->columnSpanFull(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminActivityLogs::route('/'),
            'view' => Pages\ViewAdminActivityLog::route('/{record}'),
        ];
    }
}
