<?php

namespace App\Filament\Resources\StockAdjustments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockAdjustmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('warehouse.name'),
                TextColumn::make('adjustment_date')->date()->sortable(),
                TextColumn::make('reason')->badge(),
                TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'posted', 'approved' => 'success',
                    'cancelled' => 'danger',
                    'pending_approval' => 'warning',
                    default => 'gray',
                }),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'pending_approval' => 'Pending Approval', 'approved' => 'Approved',
                    'posted' => 'Posted', 'cancelled' => 'Cancelled',
                ]),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
