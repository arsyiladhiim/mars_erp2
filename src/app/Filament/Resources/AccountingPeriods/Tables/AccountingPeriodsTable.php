<?php

namespace App\Filament\Resources\AccountingPeriods\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccountingPeriodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fiscalYear.code')->label('Fiscal Year'),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('start_date')->date(),
                TextColumn::make('end_date')->date(),
                TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'open' => 'success',
                    'processing', 'review' => 'warning',
                    'closed', 'locked' => 'danger',
                    default => 'gray',
                }),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'open' => 'Open', 'processing' => 'Processing', 'review' => 'Review',
                    'closed' => 'Closed', 'locked' => 'Locked',
                ]),
            ])
            ->defaultSort('start_date', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
