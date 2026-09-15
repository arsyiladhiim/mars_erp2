<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(200)->columnSpanFull(),
            TextInput::make('document_type')->maxLength(50)
                ->helperText('e.g. contract, invoice, quotation, signed_document'),
            FileUpload::make('file_path')->required()->directory('documents')->columnSpanFull(),
            DatePicker::make('retention_until'),
        ])->columns(2);
    }
}
