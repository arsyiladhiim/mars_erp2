<?php

namespace App\Filament\Resources\KbArticles\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class KbArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(200)->columnSpanFull(),
            TextInput::make('category')->maxLength(100),
            Select::make('author_id')->relationship('author', 'name')->searchable(),
            Toggle::make('is_published'),
            RichEditor::make('content')->columnSpanFull(),
        ])->columns(2);
    }
}
