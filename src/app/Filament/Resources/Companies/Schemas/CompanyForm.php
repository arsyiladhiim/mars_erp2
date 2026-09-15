<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Company Profile')
                    ->columns(2)
                    ->components([
                        TextInput::make('code')->required()->maxLength(20)->unique(ignoreRecord: true),
                        TextInput::make('name')->required()->maxLength(150),
                        TextInput::make('legal_name')->maxLength(150),
                        TextInput::make('tax_id')->label('NPWP')->maxLength(30),
                        TextInput::make('email')->email()->maxLength(150),
                        TextInput::make('phone')->tel()->maxLength(30),
                        TextInput::make('base_currency')->default('IDR')->maxLength(3)->required(),
                        Toggle::make('is_active')->default(true),
                    ]),
                Section::make('Address & Branding')
                    ->columns(1)
                    ->components([
                        \Filament\Forms\Components\Textarea::make('address')->rows(3),
                        FileUpload::make('logo_path')->image()->directory('company-logos'),
                    ]),
            ]);
    }
}
