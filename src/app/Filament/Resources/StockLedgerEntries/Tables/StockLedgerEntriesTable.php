<?php

namespace App\Filament\Resources\StockLedgerEntries\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockLedgerEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('movement_date')->date()->sortable(),
                TextColumn::make('item.name')->searchable()->sortable(),
                TextColumn::make('warehouse.name'),
                TextColumn::make('movement_type')->badge(),
                TextColumn::make('quantity_in')->numeric(),
                TextColumn::make('quantity_out')->numeric(),
                TextColumn::make('balance_quantity')->numeric()->weight('bold'),
                TextColumn::make('unit_cost')->money('IDR')->toggleable(),
                TextColumn::make('balance_value')->money('IDR')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('movement_type')->options([
                    'opening' => 'Opening', 'purchase_receipt' => 'Purchase Receipt',
                    'sales_delivery' => 'Sales Delivery', 'goods_issue' => 'Goods Issue',
                    'goods_receipt' => 'Goods Receipt', 'transfer_in' => 'Transfer In',
                    'transfer_out' => 'Transfer Out', 'adjustment' => 'Adjustment',
                    'return_in' => 'Return In', 'return_out' => 'Return Out', 'stock_opname' => 'Stock Opname',
                ]),
            ])
            ->defaultSort('movement_date', 'desc');
    }
}
