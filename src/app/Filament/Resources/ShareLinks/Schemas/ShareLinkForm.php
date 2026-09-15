<?php

namespace App\Filament\Resources\ShareLinks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ShareLinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('document_id')->relationship('document', 'title')->searchable()->required(),
            TextInput::make('token')->disabled()->dehydrated()->helperText('Generated automatically.'),
            DateTimePicker::make('expires_at'),
            Toggle::make('allow_download')->default(true),
            Toggle::make('requires_signature'),
            Toggle::make('is_revoked'),
        ])->columns(2);
    }
}
