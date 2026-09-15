<?php

namespace App\Filament\Resources\GoodsReceipts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GoodsReceiptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('purchaseOrder.number')->label('PO #')->toggleable(),
                TextColumn::make('businessPartner.name')->label('Supplier')->searchable(),
                TextColumn::make('warehouse.name'),
                TextColumn::make('receipt_date')->date()->sortable(),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'posted' => 'Posted', 'cancelled' => 'Cancelled',
                ]),
            ])
            ->defaultSort('receipt_date', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
