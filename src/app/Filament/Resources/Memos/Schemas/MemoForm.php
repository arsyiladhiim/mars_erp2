<?php

namespace App\Filament\Resources\Memos\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MemoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(200)->columnSpanFull(),
            Select::make('author_id')->relationship('author', 'name')->searchable()->required(),
            Select::make('visibility')->options([
                'personal' => 'Personal', 'department' => 'Department', 'shared' => 'Shared',
            ])->default('personal')->required(),
            Select::make('department_id')->relationship('department', 'name')->searchable(),
            RichEditor::make('content')->columnSpanFull(),
            TagsInput::make('tags')->columnSpanFull(),
        ])->columns(2);
    }
}
