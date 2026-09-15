<?php

namespace App\Filament\Resources\JournalEntries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class JournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('entry_date')->date()->sortable(),
                TextColumn::make('source_type')->badge(),
                TextColumn::make('total_debit')->money('IDR'),
                TextColumn::make('total_credit')->money('IDR'),
                TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'posted' => 'success',
                    'reversed' => 'danger',
                    default => 'gray',
                }),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'posted' => 'Posted', 'reversed' => 'Reversed',
                ]),
            ])
            ->defaultSort('entry_date', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
