<?php

namespace App\Filament\Resources\ShareLinks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShareLinksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document.title')->searchable()->sortable(),
                TextColumn::make('token')->copyable()->limit(20),
                TextColumn::make('expires_at')->dateTime()->toggleable(),
                IconColumn::make('allow_download')->boolean(),
                IconColumn::make('requires_signature')->boolean(),
                IconColumn::make('is_revoked')->boolean(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
