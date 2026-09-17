<?php

namespace App\Filament\Resources\GlAccountMappings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GlAccountMappingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->label('Transaction Type')->searchable(),
                TextColumn::make('chartOfAccount.code')->label('Account Code')->placeholder('— Not set —'),
                TextColumn::make('chartOfAccount.name')->label('Account Name')->placeholder('— Not set —'),
                IconColumn::make('is_configured')
                    ->label('Configured')
                    ->state(fn ($record) => filled($record->chart_of_account_id))
                    ->boolean(),
            ])
            ->recordActions([EditAction::make()])
            ->paginated(false);
    }
}
