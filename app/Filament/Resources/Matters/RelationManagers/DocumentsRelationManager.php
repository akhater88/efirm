<?php

namespace App\Filament\Resources\Matters\RelationManagers;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Services\DocumentImportService;
use App\Services\DocumentService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('documents.documents');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('documents.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('document_type')
                    ->label(__('documents.document_type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof DocumentType ? $state->label() : $state),
                TextColumn::make('status')
                    ->label(__('documents.status'))
                    ->badge()
                    ->color(fn ($state) => $state instanceof DocumentStatus ? $state->color() : 'gray')
                    ->formatStateUsing(fn ($state) => $state instanceof DocumentStatus ? $state->label() : $state),
                TextColumn::make('language_primary')
                    ->label(__('documents.language'))
                    ->formatStateUsing(fn ($state) => $state?->label()),
                TextColumn::make('currentVersion.version_number')
                    ->label(__('documents.version'))
                    ->prefix('V')
                    ->sortable(),
                TextColumn::make('createdBy.name')
                    ->label(__('common.created_by'))
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label(__('common.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('documents.status'))
                    ->options(collect(DocumentStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
                SelectFilter::make('document_type')
                    ->label(__('documents.document_type'))
                    ->options(collect(DocumentType::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
            ])
            ->headerActions([
                Action::make('import')
                    ->label(__('documents.import_docx'))
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        FileUpload::make('file')
                            ->label(__('documents.import_file'))
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(25600)
                            ->required()
                            ->directory('temp-imports')
                            ->visibility('private'),
                        TextInput::make('title')
                            ->label(__('documents.title'))
                            ->placeholder(__('documents.import_title_placeholder'))
                            ->maxLength(255),
                        Select::make('document_type')
                            ->label(__('documents.document_type'))
                            ->options(collect(DocumentType::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                            ->default('contract'),
                    ])
                    ->action(function (array $data) {
                        $matter = $this->getOwnerRecord();
                        $filePath = storage_path('app/public/'.$data['file']);

                        if (! file_exists($filePath)) {
                            // Filament may store in different location
                            $filePath = storage_path('app/'.$data['file']);
                        }

                        if (! file_exists($filePath)) {
                            Notification::make()
                                ->danger()
                                ->title(__('documents.import_error'))
                                ->body(__('documents.import_file_not_found'))
                                ->send();

                            return;
                        }

                        $uploadedFile = new UploadedFile(
                            $filePath,
                            basename($data['file']),
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            null,
                            true,
                        );

                        $importService = app(DocumentImportService::class);

                        $options = array_filter([
                            'title' => $data['title'] ?? null,
                            'document_type' => $data['document_type'] ?? null,
                        ]);

                        $importService->importDocx($uploadedFile, $matter, auth()->user(), $options);

                        // Clean up temp file
                        @unlink($filePath);

                        Notification::make()
                            ->success()
                            ->title(__('documents.import_success'))
                            ->send();
                    }),
                Action::make('create_blank')
                    ->label(__('documents.create_blank'))
                    ->icon('heroicon-o-document-plus')
                    ->form([
                        TextInput::make('title')
                            ->label(__('documents.title'))
                            ->required()
                            ->maxLength(255),
                        Select::make('document_type')
                            ->label(__('documents.document_type'))
                            ->options(collect(DocumentType::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                            ->default('contract')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $matter = $this->getOwnerRecord();
                        $documentService = app(DocumentService::class);

                        $emptyBody = [
                            'type' => 'doc',
                            'content' => [
                                [
                                    'type' => 'heading',
                                    'attrs' => ['level' => 1],
                                    'content' => [['type' => 'text', 'text' => $data['title']]],
                                ],
                                [
                                    'type' => 'paragraph',
                                    'content' => [],
                                ],
                            ],
                        ];

                        $documentService->createDocument(
                            $matter,
                            $data['title'],
                            $emptyBody,
                            auth()->user(),
                            [
                                'document_type' => $data['document_type'] ?? DocumentType::Contract,
                                'change_summary' => __('documents.blank_document_created'),
                            ],
                        );

                        Notification::make()
                            ->success()
                            ->title(__('documents.created_success'))
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('open_editor')
                    ->label(__('documents.open_editor'))
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn ($record) => route('documents.editor', [
                        'matter' => $this->getOwnerRecord()->id,
                        'document' => $record->id,
                    ])),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->emptyStateHeading(__('documents.empty_state_heading'))
            ->emptyStateDescription(__('documents.empty_state_description'))
            ->emptyStateIcon('heroicon-o-document-text');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }
}
