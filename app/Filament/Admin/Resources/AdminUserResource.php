<?php

namespace App\Filament\Admin\Resources;

use App\Enums\AdminActivityEventType;
use App\Enums\AdminRole;
use App\Filament\Admin\Resources\AdminUserResource\Pages;
use App\Models\AdminUser;
use App\Services\AdminActivityLogService;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AdminUserResource extends Resource
{
    protected static ?string $model = AdminUser::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.admin_users');
    }

    public static function getModelLabel(): string
    {
        return __('admin.users.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.users.plural');
    }

    public static function canAccess(): bool
    {
        $admin = auth()->guard('admin')->user();

        return $admin instanceof AdminUser && $admin->isSuperAdmin();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label(__('admin.users.name'))
                    ->required()
                    ->maxLength(100),

                TextInput::make('email')
                    ->label(__('admin.users.email'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Select::make('role')
                    ->label(__('admin.users.role'))
                    ->options(collect(AdminRole::cases())->mapWithKeys(
                        fn (AdminRole $role) => [$role->value => $role->label()]
                    ))
                    ->required(),

                Select::make('locale')
                    ->label(__('admin.users.locale'))
                    ->options([
                        'ar' => __('admin.locales.ar'),
                        'en' => __('admin.locales.en'),
                    ])
                    ->default('ar')
                    ->required(),

                TextInput::make('password')
                    ->label(__('admin.users.password'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->minLength(config('admin.password.min_length', 12))
                    ->confirmed()
                    ->visibleOn('create'),

                TextInput::make('password_confirmation')
                    ->label(__('admin.users.password_confirmation'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->visibleOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.users.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('admin.users.email'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role')
                    ->label(__('admin.users.role'))
                    ->badge()
                    ->color(fn (AdminRole $state): string => $state->color())
                    ->formatStateUsing(fn (AdminRole $state): string => $state->label())
                    ->sortable(),

                TextColumn::make('last_login_at')
                    ->label(__('admin.users.last_login'))
                    ->since()
                    ->sortable(),

                TextColumn::make('disabled_at')
                    ->label(__('admin.users.status'))
                    ->badge()
                    ->getStateUsing(fn (AdminUser $record): string => $record->isDisabled()
                        ? __('admin.users.status_disabled')
                        : __('admin.users.status_active')
                    )
                    ->color(fn (AdminUser $record): string => $record->isDisabled() ? 'danger' : 'success'),

                TextColumn::make('created_at')
                    ->label(__('admin.users.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Action::make('resetPassword')
                    ->label(__('admin.users.reset_password'))
                    ->icon(Heroicon::OutlinedKey)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.users.reset_password_heading'))
                    ->modalDescription(__('admin.users.reset_password_description'))
                    ->action(function (AdminUser $record): void {
                        $newPassword = Str::random(config('admin.password.reset_length', 16));
                        $record->update(['password' => $newPassword]);

                        /** @var AdminUser $currentAdmin */
                        $currentAdmin = auth()->guard('admin')->user();

                        AdminActivityLogService::log(
                            eventType: AdminActivityEventType::UserPasswordReset,
                            admin: $currentAdmin,
                            target: $record,
                        );

                        // Flash the generated password for the admin to communicate securely
                        session()->flash('reset-password-'.$record->id, $newPassword);
                    })
                    ->after(function (AdminUser $record, Action $action): void {
                        $newPassword = session('reset-password-'.$record->id);
                        if ($newPassword) {
                            $action->sendSuccessNotification();
                        }
                    })
                    ->successNotificationTitle(__('admin.users.password_was_reset')),

                Action::make('toggleDisabled')
                    ->label(fn (AdminUser $record): string => $record->isDisabled()
                        ? __('admin.users.reenable')
                        : __('admin.users.disable')
                    )
                    ->icon(fn (AdminUser $record): BackedEnum => $record->isDisabled()
                        ? Heroicon::OutlinedCheckCircle
                        : Heroicon::OutlinedNoSymbol
                    )
                    ->color(fn (AdminUser $record): string => $record->isDisabled() ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->action(function (AdminUser $record): void {
                        /** @var AdminUser $currentAdmin */
                        $currentAdmin = auth()->guard('admin')->user();

                        if ($record->isDisabled()) {
                            $record->update(['disabled_at' => null]);
                            AdminActivityLogService::log(
                                eventType: AdminActivityEventType::UserReenabled,
                                admin: $currentAdmin,
                                target: $record,
                            );
                        } else {
                            $record->update(['disabled_at' => now()]);
                            AdminActivityLogService::log(
                                eventType: AdminActivityEventType::UserDisabled,
                                admin: $currentAdmin,
                                target: $record,
                            );
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminUsers::route('/'),
            'create' => Pages\CreateAdminUser::route('/create'),
            'edit' => Pages\EditAdminUser::route('/{record}/edit'),
        ];
    }
}
