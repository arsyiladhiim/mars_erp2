<?php

namespace App\Filament\Resources\MaintenanceRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MaintenanceRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('title')->searchable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('technician.name')->toggleable(),
                TextColumn::make('scheduled_date')->date(),
                TextColumn::make('cost')->money('IDR')->toggleable(),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'requested' => 'Requested', 'scheduled' => 'Scheduled', 'in_progress' => 'In Progress',
                    'completed' => 'Completed', 'cancelled' => 'Cancelled',
                ]),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
