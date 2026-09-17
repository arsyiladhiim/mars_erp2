<?php

namespace App\Filament\Resources\StockOpnames\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockOpnamesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('warehouse.name'),
                TextColumn::make('count_date')->date()->sortable(),
                TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'posted', 'approved' => 'success',
                    'cancelled' => 'danger',
                    'pending_approval', 'counting' => 'warning',
                    default => 'gray',
                }),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'counting' => 'Counting', 'pending_approval' => 'Pending Approval',
                    'approved' => 'Approved', 'posted' => 'Posted', 'cancelled' => 'Cancelled',
                ]),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
