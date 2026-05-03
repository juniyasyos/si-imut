<?php

namespace App\Filament\Resources\UserResource\Schema;

use App\Filament\Resources\UserResource;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;

class UserResourceSchema
{
    public static function make(): array
    {
        return [
            Section::make(__('filament-forms::users.form.user.title'))
                ->description(__('filament-forms::users.form.user.description'))
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('nip')
                            ->label(__('filament-forms::users.fields.nip'))
                            ->placeholder(__('filament-forms::users.form.user.nip_placeholder'))
                            ->required()
                            ->unique('users', 'nip', ignoreRecord: true)
                            ->maxLength(20),

                        TextInput::make('name')
                            ->label(__('filament-forms::users.fields.name'))
                            ->placeholder(__('filament-forms::users.form.user.name_placeholder'))
                            ->required(),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('place_of_birth')
                            ->label(__('filament-forms::users.fields.place_of_birth'))
                            ->placeholder(__('filament-forms::users.form.personal_info.place_of_birth_placeholder'))
                            ->nullable(),

                        DatePicker::make('date_of_birth')
                            ->label(__('filament-forms::users.fields.date_of_birth'))
                            ->placeholder(__('filament-forms::users.form.personal_info.date_of_birth_placeholder'))
                            ->nullable(),
                    ]),
                    ToggleButtons::make('gender')
                        ->label(__('filament-forms::users.fields.gender'))
                        ->options([
                            'Laki-laki' => __('filament-forms::users.form.personal_info.gender_male'),
                            'Perempuan' => __('filament-forms::users.form.personal_info.gender_female'),
                        ])
                        ->required()
                        ->inline()
                        ->colors([
                            'Laki-laki' => 'primary',
                            'Perempuan' => 'success',
                        ]),
                ]),

            Section::make(__('filament-forms::users.form.contact_info.title'))
                ->description(__('filament-forms::users.form.contact_info.description'))
                ->schema([
                    Textarea::make('address_ktp')
                        ->label(__('filament-forms::users.fields.address_ktp'))
                        ->placeholder(__('filament-forms::users.form.contact_info.address_placeholder'))
                        ->required(),

                    Grid::make(2)->schema([
                        TextInput::make('phone_number')
                            ->label(__('filament-forms::users.fields.phone_number'))
                            ->placeholder(__('filament-forms::users.form.contact_info.phone_number_placeholder'))
                            ->tel()
                            ->nullable(),

                        TextInput::make('email')
                            ->label(__('filament-forms::users.fields.email'))
                            ->placeholder(__('filament-forms::users.form.user.email_placeholder'))
                            ->email()
                            ->nullable()
                            ->unique('users', 'email', ignoreRecord: true),
                    ]),
                ]),

            Section::make(__('filament-forms::users.form.account.title'))
                ->description(__('filament-forms::users.form.account.description'))
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('password')
                            ->label(__('filament-forms::users.fields.password'))
                            ->placeholder(__('filament-forms::users.form.user.password_placeholder'))
                            ->password()
                            ->dehydrateStateUsing(fn($state) => $state ? bcrypt($state) : null)
                            ->required(fn($livewire) => $livewire instanceof CreateRecord),

                        ToggleButtons::make('status')
                            ->label(__('filament-forms::users.fields.status'))
                            ->options([
                                'active' => __('filament-forms::users.status.active'),
                                'inactive' => __('filament-forms::users.status.inactive'),
                                'suspended' => __('filament-forms::users.status.suspended'),
                            ])
                            ->required()
                            ->default('active')
                            ->inline()
                            ->colors([
                                'active' => 'success',
                                'inactive' => 'warning',
                                'suspended' => 'danger',
                            ]),
                    ]),
                ]),

            Section::make(__('filament-forms::users.form.documents.title'))
                ->description(__('filament-forms::users.form.documents.description'))
                ->schema([
                    FileUpload::make('ttd_url')
                        ->label(__('filament-forms::users.fields.ttd_url'))
                        ->disk(\App\Support\StorageFallback::isS3Available() ? 's3' : 'public')
                        ->directory('ttd')
                        ->image()
                        ->openable()
                        ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                        ->maxSize(2048)
                        ->nullable(),
                ]),

            Section::make(__('filament-forms::users.form.roles.title'))
                ->description(__('filament-forms::users.form.roles.description'))
                ->schema([
                    Select::make('roles')
                        ->label(__('filament-forms::users.fields.roles'))
                        ->relationship('roles', 'name')
                        // ->multiple()
                        ->preload()
                        ->searchable()
                        ->placeholder(__('filament-forms::users.form.roles.select_placeholder'))
                        ->required(),
                ]),
        ];
    }
}
