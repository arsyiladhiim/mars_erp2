<?php

namespace App\Filament\Resources\Rfqs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RfqsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('purchaseRequest.number')->label('PR #')->toggleable(),
                TextColumn::make('suppliers_count')->counts('suppliers')->label('Suppliers'),
                TextColumn::make('required_date')->date(),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'sent' => 'Sent', 'responded' => 'Responded',
                    'closed' => 'Closed', 'cancelled' => 'Cancelled',
                ]),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
