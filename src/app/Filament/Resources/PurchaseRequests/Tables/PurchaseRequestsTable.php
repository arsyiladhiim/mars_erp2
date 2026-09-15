<?php

namespace App\Filament\Resources\PurchaseRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PurchaseRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('requester.name')->searchable(),
                TextColumn::make('department.name')->toggleable(),
                TextColumn::make('required_date')->date(),
                TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'approved' => 'success',
                    'rejected', 'cancelled' => 'danger',
                    'pending_approval', 'submitted' => 'warning',
                    default => 'gray',
                }),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'submitted' => 'Submitted', 'pending_approval' => 'Pending Approval',
                    'approved' => 'Approved', 'rejected' => 'Rejected', 'closed' => 'Closed',
                    'cancelled' => 'Cancelled',
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
