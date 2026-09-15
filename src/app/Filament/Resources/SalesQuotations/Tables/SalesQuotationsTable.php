<?php

namespace App\Filament\Resources\SalesQuotations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SalesQuotationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('businessPartner.name')->label('Customer')->searchable(),
                TextColumn::make('quotation_date')->date()->sortable(),
                TextColumn::make('validity_date')->date(),
                TextColumn::make('grand_total')->money('IDR'),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'sent' => 'Sent', 'accepted' => 'Accepted',
                    'declined' => 'Declined', 'expired' => 'Expired', 'converted' => 'Converted',
                ]),
            ])
            ->defaultSort('quotation_date', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
