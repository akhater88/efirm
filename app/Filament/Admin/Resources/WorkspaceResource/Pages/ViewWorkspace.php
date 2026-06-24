<?php

namespace App\Filament\Admin\Resources\WorkspaceResource\Pages;

use App\Enums\AdminRole;
use App\Filament\Admin\Resources\WorkspaceResource;
use App\Models\AdminUser;
use App\Models\User;
use App\Models\Workspace;
use App\Services\AdminImpersonationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ViewWorkspace extends ViewRecord
{
    protected static string $resource = WorkspaceResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('admin.workspaces.section_details'))
                ->schema([
                    TextEntry::make('name')->label(__('admin.workspaces.name')),
                    TextEntry::make('slug')->label(__('admin.workspaces.slug')),
                    TextEntry::make('default_locale')->label(__('admin.workspaces.locale')),
                    TextEntry::make('created_at')->label(__('admin.workspaces.created_at'))->dateTime(),
                    TextEntry::make('members_count')
                        ->label(__('admin.workspaces.members_count'))
                        ->getStateUsing(fn (Workspace $record) => $record->members()->count()),
                ])->columns(3),

            Section::make(__('admin.workspaces.section_pdpl'))
                ->schema([
                    TextEntry::make('pdpl_consent_obtained')
                        ->label(__('admin.workspaces.pdpl_consent'))
                        ->badge()
                        ->getStateUsing(fn (Workspace $record) => $record->pdpl_consent_obtained ? __('admin.workspaces.consent_yes') : __('admin.workspaces.consent_no')),
                    TextEntry::make('pdpl_consent_date')->label(__('admin.workspaces.consent_date'))->dateTime(),
                    TextEntry::make('pdpl_consent_text_version')->label(__('admin.workspaces.consent_version')),
                ])->columns(3),
        ]);
    }

    protected function getHeaderActions(): array
    {
        /** @var AdminUser $admin */
        $admin = auth()->guard('admin')->user();

        $actions = [];

        if (in_array($admin->role, [AdminRole::SuperAdmin, AdminRole::Support])) {
            $actions[] = Action::make('impersonate')
                ->label(__('admin.impersonation.start'))
                ->icon('heroicon-o-eye')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading(__('admin.impersonation.modal_heading'))
                ->modalDescription(__('admin.impersonation.modal_description'))
                ->form([
                    Select::make('user_id')
                        ->label(__('admin.impersonation.select_user'))
                        ->options(fn () => $this->record->users()->pluck('name', 'users.id'))
                        ->required(),
                    TextInput::make('purpose')
                        ->label(__('admin.impersonation.purpose'))
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data) use ($admin): void {
                    $user = User::findOrFail($data['user_id']);
                    $service = app(AdminImpersonationService::class);

                    try {
                        $service->start($admin, $user, $this->record, $data['purpose']);
                        $this->redirect('/dashboard');
                    } catch (ConflictHttpException $e) {
                        $this->notify('warning', $e->getMessage());
                    }
                });
        }

        return $actions;
    }
}
