<?php

namespace App\Filament\Resources\AiProviders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AiProvidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('base_url')->limit(40)->toggleable(),
                TextColumn::make('default_model')->badge(),
                TextColumn::make('api_key')
                    ->label('API Key')
                    ->formatStateUsing(fn ($state) => filled($state) ? '••••••••' : '—'),
                IconColumn::make('is_active')->label('Active')->boolean(),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
