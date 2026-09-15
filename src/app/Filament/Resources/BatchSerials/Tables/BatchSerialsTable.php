<?php

namespace App\Filament\Resources\BatchSerials\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BatchSerialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item.name')->searchable()->sortable(),
                TextColumn::make('warehouse.name'),
                TextColumn::make('type')->badge(),
                TextColumn::make('batch_number'),
                TextColumn::make('serial_number'),
                TextColumn::make('quantity')->numeric(),
                TextColumn::make('expiry_date')->date()->toggleable(),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'available' => 'Available', 'reserved' => 'Reserved', 'sold' => 'Sold',
                    'expired' => 'Expired', 'quarantine' => 'Quarantine',
                ]),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
