<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('contacts.basic_info'))
                    ->schema([
                        Select::make('type')
                            ->label(__('contacts.type'))
                            ->options([
                                'person' => __('contacts.person'),
                                'organization' => __('contacts.organization'),
                            ])
                            ->required()
                            ->reactive()
                            ->default('person'),
                        TextInput::make('first_name')
                            ->label(__('contacts.first_name'))
                            ->required(fn ($get) => $get('type') === 'person')
                            ->visible(fn ($get) => $get('type') === 'person')
                            ->maxLength(255),
                        TextInput::make('middle_name')
                            ->label(__('contacts.middle_name'))
                            ->visible(fn ($get) => $get('type') === 'person')
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label(__('contacts.last_name'))
                            ->required(fn ($get) => $get('type') === 'person')
                            ->visible(fn ($get) => $get('type') === 'person')
                            ->maxLength(255),
                        TextInput::make('organization_name')
                            ->label(__('contacts.organization_name'))
                            ->required(fn ($get) => $get('type') === 'organization')
                            ->visible(fn ($get) => $get('type') === 'organization')
                            ->maxLength(255),
                    ])->columns(2),

                Section::make(__('contacts.contact_info'))
                    ->schema([
                        TextInput::make('email')
                            ->label(__('contacts.email'))
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('contacts.phone'))
                            ->tel()
                            ->maxLength(50),
                    ])->columns(2),

                Section::make(__('contacts.classification'))
                    ->schema([
                        TextInput::make('nationality')
                            ->label(__('contacts.nationality'))
                            ->maxLength(2)
                            ->helperText('ISO 3166-1 alpha-2'),
                        TextInput::make('tax_registration_number')
                            ->label(__('contacts.tax_registration_number'))
                            ->maxLength(100),
                        Toggle::make('is_client')
                            ->label(__('contacts.client')),
                        Toggle::make('is_counterparty')
                            ->label(__('contacts.counterparty')),
                        Select::make('parent_organization_id')
                            ->label(__('contacts.parent_organization'))
                            ->relationship('parentOrganization', 'display_name', fn ($query) => $query->where('type', 'organization'))
                            ->visible(fn ($get) => $get('type') === 'person')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Section::make(__('contacts.address'))
                    ->schema([
                        TextInput::make('address_line_1')
                            ->label(__('contacts.address_line_1'))
                            ->maxLength(255),
                        TextInput::make('address_line_2')
                            ->label(__('contacts.address_line_2'))
                            ->maxLength(255),
                        TextInput::make('city')
                            ->label(__('contacts.city'))
                            ->maxLength(100),
                        TextInput::make('country')
                            ->label(__('contacts.country'))
                            ->maxLength(2)
                            ->helperText('ISO 3166-1 alpha-2'),
                    ])->columns(2),

                Section::make(__('contacts.notes'))
                    ->schema([
                        Textarea::make('notes')
                            ->label(__('contacts.notes'))
                            ->rows(4)
                            ->columnSpanFull(),
                        TagsInput::make('labels')
                            ->label(__('contacts.labels')),
                    ]),
            ]);
    }
}
